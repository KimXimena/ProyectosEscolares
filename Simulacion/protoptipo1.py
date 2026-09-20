# mi_shell_gui.py
import os
import shlex
import subprocess
import sys
import threading
import time
from datetime import datetime
import tkinter as tk
from tkinter import filedialog, messagebox, scrolledtext

# Requiere psutil y matplotlib
import psutil
import shutil
import matplotlib.pyplot as plt
from matplotlib.backends.backend_tkagg import FigureCanvasTkAgg

# ---------- Config ----------
HISTORY_FILE = os.path.expanduser("~/.mi_shell_history")
HISTORY_MAX = 200

# ---------- Utilidades ----------
def safe_run_subprocess(cmd_list, cwd=None, timeout=10):
    try:
        completed = subprocess.run(cmd_list, capture_output=True, text=True, cwd=cwd, timeout=timeout, shell=False)
        out = completed.stdout or ""
        err = completed.stderr or ""
        if completed.returncode != 0 and not out:
            out = err
        return out
    except Exception as e:
        return f"Error ejecutando comando externo: {e}"

# ---------- Built-in commands ----------
def cmd_miinfo():
    return f"Sistema: {os.name} | Plataforma: {sys.platform}\nUsuario: {os.getlogin()}\nHome: {os.path.expanduser('~')}"

def cmd_limpiar(ui):
    ui.output_text.configure(state="normal")
    ui.output_text.delete("1.0", tk.END)
    ui.output_text.configure(state="disabled")
    return ""

def cmd_hora():
    return datetime.now().strftime("%H:%M:%S")

def cmd_fecha():
    return datetime.now().strftime("%Y-%m-%d")

def cmd_listar(path):
    try:
        items = os.listdir(path)
        return "\n".join(items)
    except Exception as e:
        return f"Error listar: {e}"

def cmd_memdetallada():
    vm = psutil.virtual_memory()
    return (f"Total: {vm.total} bytes\nAvailable: {vm.available}\nUsed: {vm.used}\nPercent: {vm.percent}%")

def cmd_cpucores():
    per = psutil.cpu_percent(percpu=True)
    lines = [f"Núcleo {i}: {p}%" for i,p in enumerate(per)]
    return "\n".join(lines)

def cmd_pinglocal():
    # Simple ping local loopback (cross-platform)
    target = "127.0.0.1"
    param = ["-n", "1"] if os.name == "nt" else ["-c", "1"]
    cmd = ["ping"] + param + [target]
    return safe_run_subprocess(cmd)

def cmd_recursos():
    cpu = psutil.cpu_percent()
    ram = psutil.virtual_memory().percent
    return f"CPU: {cpu}%\nRAM: {ram}%"

def cmd_top5():
    procs = sorted(psutil.process_iter(['pid','name','cpu_percent','memory_percent']), key=lambda p: (p.info['cpu_percent'] or 0)+(p.info['memory_percent'] or 0), reverse=True)[:5]
    lines=[]
    for p in procs:
        info=p.info
        lines.append(f"PID:{info['pid']} {info['name']} CPU:{info['cpu_percent']}% RAM:{info['memory_percent']:.2f}%")
    return "\n".join(lines)

def cmd_usodisco():
    du = shutil.disk_usage("/")
    return f"Total: {du.total} bytes\nUsed: {du.used} bytes\nFree: {du.free} bytes"

def cmd_usodiscodet():
    out=[]
    for part in psutil.disk_partitions(all=True):
        try:
            usage=psutil.disk_usage(part.mountpoint)
            out.append(f"{part.device} ({part.mountpoint}) - total:{usage.total} used:{usage.used} free:{usage.free} percent:{usage.percent}%")
        except Exception:
            continue
    return "\n".join(out)

# Grafica disco principal (pastel)
def cmd_graficadisco_embed(parent_frame):
    du = shutil.disk_usage("/")
    labels = ["Used","Free"]
    sizes = [du.used, du.free]
    fig, ax = plt.subplots(figsize=(4,3))
    ax.pie(sizes, labels=labels, autopct='%1.1f%%')
    ax.set_title("Uso disco raíz")
    canvas = FigureCanvasTkAgg(fig, master=parent_frame)
    canvas.draw()
    canvas.get_tk_widget().pack(side="top", fill="both", expand=True)
    return canvas

# Gráfica detallada por partición
def cmd_gdd_embed(parent_frame):
    labels=[]
    sizes=[]
    for part in psutil.disk_partitions(all=False):
        try:
            u=psutil.disk_usage(part.mountpoint)
            labels.append(part.device)
            sizes.append(u.used)
        except Exception:
            continue
    if not sizes:
        fig, ax = plt.subplots(figsize=(4,3))
        ax.text(0.5,0.5,"No se detectaron particiones", ha='center')
    else:
        fig, ax = plt.subplots(figsize=(4,3))
        ax.pie(sizes, labels=labels, autopct='%1.1f%%')
        ax.set_title("Uso por partición (en bytes usados)")
    canvas = FigureCanvasTkAgg(fig, master=parent_frame)
    canvas.draw()
    canvas.get_tk_widget().pack(side="top", fill="both", expand=True)
    return canvas

# ---------- UI ----------
class ShellGUI(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("Mi Shell Personalizado")
        self.geometry("900x600")
        # Ruta actual label
        self.cwd = os.getcwd()
        self.path_label = tk.Label(self, text=f"Ruta: {self.cwd}", anchor="w")
        self.path_label.pack(fill="x")
        # Output
        self.output_text = scrolledtext.ScrolledText(self, height=22, state="disabled", wrap='word')
        self.output_text.pack(fill="both", expand=True, padx=6, pady=6)
        # Command frame
        cmd_frame = tk.Frame(self)
        cmd_frame.pack(fill="x", padx=6, pady=4)
        self.cmd_entry = tk.Entry(cmd_frame)
        self.cmd_entry.pack(side="left", fill="x", expand=True)
        self.cmd_entry.bind("<Return>", self.on_enter)
        self.cmd_entry.bind("<Up>", self.history_up)
        self.cmd_entry.bind("<Down>", self.history_down)
        btn_run = tk.Button(cmd_frame, text="Ejecutar", command=lambda: self.on_enter(None))
        btn_run.pack(side="left", padx=4)
        btn_export = tk.Button(cmd_frame, text="Exportar salida", command=self.export_output)
        btn_export.pack(side="left", padx=4)
        btn_help = tk.Button(cmd_frame, text="Ayuda", command=self.cmd_ayuda)
        btn_help.pack(side="left", padx=4)

        self.history = []
        self.hpos = None
        self.load_history()

        # area para gráficas embebidas
        self.fig_frame = tk.Frame(self)
        self.fig_frame.pack(fill="both", expand=False, padx=6, pady=4)

    def append_output(self, text):
        if text is None or text == "":
            return
        self.output_text.configure(state="normal")
        self.output_text.insert(tk.END, text + "\n")
        self.output_text.see(tk.END)
        self.output_text.configure(state="disabled")

    def export_output(self):
        content = self.output_text.get("1.0", tk.END)
        fn = filedialog.asksaveasfilename(defaultextension=".txt", filetypes=[("Text files","*.txt")])
        if fn:
            with open(fn,"w",encoding="utf-8") as f:
                f.write(content)
            messagebox.showinfo("Exportar", f"Salida exportada en {fn}")

    def cmd_ayuda(self):
        ayuda = (
            "Comandos extendidos disponibles:\n"
            "miinfo, limpiar, hora, fecha, listar [path], memdetallada, cpucores,\n"
            "pinglocal, recursos, top5, usodisco, usodiscodet, graficadisco, gdd, exportar <archivo>, salir, ayuda\n"
            "También puedes ejecutar comandos externos del sistema (ej: ls, dir, ping, notepad, calc, tasklist)\n"
        )
        self.append_output(ayuda)

    def save_history(self):
        try:
            with open(HISTORY_FILE,"w",encoding="utf-8") as f:
                f.write("\n".join(self.history[-HISTORY_MAX:]))
        except Exception:
            pass

    def load_history(self):
        try:
            if os.path.exists(HISTORY_FILE):
                with open(HISTORY_FILE,"r",encoding="utf-8") as f:
                    self.history = [l.strip() for l in f if l.strip()]
        except Exception:
            self.history=[]

    def history_up(self, event):
        if not self.history:
            return "break"
        if self.hpos is None:
            self.hpos = len(self.history)-1
        else:
            self.hpos = max(0, self.hpos-1)
        self.cmd_entry.delete(0,tk.END)
        self.cmd_entry.insert(0, self.history[self.hpos])
        return "break"

    def history_down(self, event):
        if not self.history:
            return "break"
        if self.hpos is None:
            return "break"
        self.hpos = min(len(self.history)-1, self.hpos+1)
        self.cmd_entry.delete(0,tk.END)
        self.cmd_entry.insert(0, self.history[self.hpos])
        return "break"

    def on_enter(self, event):
        line = self.cmd_entry.get().strip()
        if not line:
            return
        # push history
        self.history.append(line)
        self.save_history()
        self.hpos = None
        # show command
        self.append_output(f"> {line}")
        # parse
        parts = shlex.split(line)
        cmd = parts[0]
        args = parts[1:]
        # clear figs area
        for w in self.fig_frame.winfo_children():
            w.destroy()

        # builtins
        if cmd == "miinfo":
            self.append_output(cmd_miinfo())
        elif cmd == "limpiar":
            cmd_limpiar(self)
        elif cmd == "hora":
            self.append_output(cmd_hora())
        elif cmd == "fecha":
            self.append_output(cmd_fecha())
        elif cmd == "listar":
            target = args[0] if args else self.cwd
            self.append_output(cmd_listar(target))
        elif cmd == "memdetallada":
            self.append_output(cmd_memdetallada())
        elif cmd == "cpucores":
            self.append_output(cmd_cpucores())
        elif cmd == "pinglocal":
            self.append_output(cmd_pinglocal())
        elif cmd == "recursos":
            self.append_output(cmd_recursos())
        elif cmd == "top5":
            self.append_output(cmd_top5())
        elif cmd == "usodisco":
            self.append_output(cmd_usodisco())
        elif cmd == "usodiscodet":
            self.append_output(cmd_usodiscodet())
        elif cmd == "graficadisco":
            # embed chart
            try:
                cmd_graficadisco_embed(self.fig_frame)
            except Exception as e:
                self.append_output(f"Error graficando: {e}")
        elif cmd == "gdd":
            try:
                cmd_gdd_embed(self.fig_frame)
            except Exception as e:
                self.append_output(f"Error graficando particiones: {e}")
        elif cmd == "exportar":
            if args:
                fname = args[0]
                try:
                    with open(fname,"w",encoding="utf-8") as f:
                        f.write(self.output_text.get("1.0", tk.END))
                    self.append_output(f"Exportado a {fname}")
                except Exception as e:
                    self.append_output(f"Error exportando: {e}")
            else:
                self.append_output("Uso: exportar nombre_archivo.txt")
        elif cmd == "salir":
            self.destroy()
        elif cmd == "ayuda":
            self.cmd_ayuda()
        else:
            # intentar comando externo (soportar comandos como cd)
            if cmd == "cd":
                target = args[0] if args else os.path.expanduser("~")
                try:
                    os.chdir(target)
                    self.cwd = os.getcwd()
                    self.path_label.config(text=f"Ruta: {self.cwd}")
                except Exception as e:
                    self.append_output(f"cd error: {e}")
            else:
                # Ejecutar externo (no shell) - dividir en lista
                try:
                    # Si en Windows y el comando es internal (dir), usar shell=True es necesario
                    if os.name == "nt":
                        # intentar directamente
                        out = safe_run_subprocess([cmd]+args, cwd=self.cwd)
                    else:
                        out = safe_run_subprocess([cmd]+args, cwd=self.cwd)
                    self.append_output(out)
                except Exception as e:
                    self.append_output(f"Error: {e}")

        # actualizar ruta label por si cd sucedió
        try:
            self.cwd = os.getcwd()
            self.path_label.config(text=f"Ruta: {self.cwd}")
        except Exception:
            pass

        self.cmd_entry.delete(0,tk.END)

if __name__ == "__main__":
    app = ShellGUI()
    app.cmd_ayuda()  # mostrar ayuda inicial
    app.mainloop()
