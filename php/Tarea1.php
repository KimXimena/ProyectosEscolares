<?php
class GestorTareas {
    private $tareas = [];
    
    public function agregarTarea($titulo, $descripcion) {
        $this->tareas[] = [
            'id' => count($this->tareas) + 1,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'completada' => false,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];
        return "Tarea '$titulo' agregada correctamente.";
    }
    
    public function completarTarea($id) {
        foreach($this->tareas as &$tarea) {
            if($tarea['id'] == $id) {
                $tarea['completada'] = true;
                return "Tarea '{$tarea['titulo']}' marcada como completada.";
            }
        }
        return "Tarea no encontrada.";
    }
    
    public function mostrarTareas() {
        echo "<h3>Lista de Tareas:</h3>";
        foreach($this->tareas as $tarea) {
            $estado = $tarea['completada'] ? '✅ Completada' : '⏳ Pendiente';
            echo "{$tarea['id']}. {$tarea['titulo']} - $estado<br>";
            echo "   Descripción: {$tarea['descripcion']}<br>";
            echo "   Creada: {$tarea['fecha_creacion']}<br><br>";
        }
    }
}

// Uso del sistema
$gestor = new GestorTareas();
$gestor->agregarTarea("Aprender PHP", "Estudiar conceptos básicos de PHP");
$gestor->agregarTarea("Crear proyecto", "Desarrollar aplicación web");
$gestor->completarTarea(1);
$gestor->mostrarTareas();
?>