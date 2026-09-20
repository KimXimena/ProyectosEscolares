import java.util.List;
import java.util.Scanner;
import java.util.concurrent.*;

public class SimuladorExamen {

    private static final Scanner scanner = new Scanner(System.in);
    private static final UsuarioDAO usuarioDAO = new UsuarioDAO();
    private static final ExamenDAO examenDAO = new ExamenDAO();

    public static void main(String[] args) {
        while (true) {
            System.out.println("\n===== SIMULADOR DE EXAMEN DE MANEJO =====");
            System.out.println("1. Registrarse");
            System.out.println("2. Iniciar sesión");
            System.out.println("3. Salir");
            System.out.print("Elige una opción: ");

            String opcion = scanner.nextLine();

            switch (opcion) {
                case "1":
                    registrar();
                    break;
                case "2":
                    Usuario usuario = login();
                    if (usuario != null) {
                        menuUsuario(usuario);
                    } else {
                        System.out.println("Correo o contraseña incorrectos.");
                    }
                    break;
                case "3":
                    System.out.println("Saliendo...");
                    return;
                default:
                    System.out.println("Opción no válida.");
            }
        }
    }

    private static void registrar() {
        System.out.print("Nombre: ");
        String nombre = scanner.nextLine();

        System.out.print("Correo: ");
        String correo = scanner.nextLine();

        System.out.print("Contraseña: ");
        String contrasena = scanner.nextLine();

        boolean registrado = usuarioDAO.registrarUsuario(nombre, correo, contrasena);

        if (registrado) {
            System.out.println("Usuario registrado correctamente.");
        }
    }

    private static Usuario login() {
        System.out.print("Correo: ");
        String correo = scanner.nextLine();

        System.out.print("Contraseña: ");
        String contrasena = scanner.nextLine();

        return usuarioDAO.login(correo, contrasena);
    }

    private static void menuUsuario(Usuario usuario) {
        while (true) {
            usuario = usuarioDAO.obtenerUsuarioPorId(usuario.getIdUsuario());

            System.out.println("\n===== BIENVENIDO, " + usuario.getNombre() + " =====");
            System.out.println("Intentos simulador usados: " + usuario.getIntentosSimulador() + "/6");
            System.out.println("Intentos final usados: " + usuario.getIntentosFinal() + "/3");
            System.out.println("1. Presentar simulador de práctica (20 preguntas)");
            System.out.println("2. Presentar examen final (40 preguntas)");
            System.out.println("3. Cerrar sesión");
            System.out.print("Elige una opción: ");

            String opcion = scanner.nextLine();

            switch (opcion) {
                case "1":
                    if (usuario.getIntentosSimulador() >= 6) {
                        System.out.println("Ya agotaste tus 6 intentos del simulador.");
                    } else {
                        usuarioDAO.incrementarIntentoSimulador(usuario.getIdUsuario());
                        presentarExamen(usuario, "simulador", 20, 5.0);
                    }
                    break;

                case "2":
                    if (usuario.getIntentosFinal() >= 3) {
                        System.out.println("Ya agotaste tus 3 intentos del examen final.");
                    } else {
                        usuarioDAO.incrementarIntentoFinal(usuario.getIdUsuario());
                        presentarExamen(usuario, "final", 40, 2.5);
                    }
                    break;

                case "3":
                    System.out.println("Sesión cerrada.");
                    return;

                default:
                    System.out.println("Opción no válida.");
            }
        }
    }

    private static void presentarExamen(Usuario usuario, String tipo, int cantidadPreguntas, double valorPregunta) {
        List<Pregunta> preguntas = examenDAO.obtenerPreguntasAleatorias(cantidadPreguntas);

        int correctas = 0;
        int numeroPregunta = 1;

        System.out.println("\n===== INICIO DEL " + tipo.toUpperCase() + " =====");
        System.out.println("Tienes 1 minuto por pregunta.\n");

        for (Pregunta pregunta : preguntas) {
            System.out.println("Pregunta " + numeroPregunta + ": " + pregunta.getTextoPregunta());

            if (pregunta.getImagen() != null && !pregunta.getImagen().trim().isEmpty()) {
                System.out.println("Imagen: " + pregunta.getImagen());
            }

            List<Opcion> opciones = pregunta.getOpciones();
            for (int i = 0; i < opciones.size(); i++) {
                System.out.println((i + 1) + ". " + opciones.get(i).getTextoOpcion());
            }

            Integer respuesta = leerRespuestaConTiempo(60);

            if (respuesta == null) {
                System.out.println("Tiempo agotado. Respuesta marcada como incorrecta.\n");
            } else if (respuesta < 1 || respuesta > opciones.size()) {
                System.out.println("Respuesta inválida. Se toma como incorrecta.\n");
            } else {
                Opcion opcionElegida = opciones.get(respuesta - 1);
                if (opcionElegida.isCorrecta()) {
                    correctas++;
                    System.out.println("Correcta.\n");
                } else {
                    System.out.println("Incorrecta.\n");
                }
            }

            numeroPregunta++;
        }

        double puntaje = correctas * valorPregunta;
        double porcentaje = (puntaje / 100.0) * 100.0;
        String resultado = porcentaje >= 75.0 ? "APROBADO" : "NO APROBADO";

        System.out.println("===== RESULTADO =====");
        System.out.println("Usuario: " + usuario.getNombre());
        System.out.println("Tipo de examen: " + tipo);
        System.out.println("Respuestas correctas: " + correctas + "/" + cantidadPreguntas);
        System.out.println("Calificación: " + puntaje + "/100");
        System.out.println("Porcentaje: " + porcentaje + "%");
        System.out.println("Resultado final: " + resultado);
    }

    private static Integer leerRespuestaConTiempo(int segundos) {
        ExecutorService executor = Executors.newSingleThreadExecutor();

        Callable<Integer> tarea = () -> {
            System.out.print("Tu respuesta (número): ");
            String entrada = scanner.nextLine();
            return Integer.parseInt(entrada);
        };

        Future<Integer> futuro = executor.submit(tarea);

        try {
            return futuro.get(segundos, TimeUnit.SECONDS);
        } catch (TimeoutException e) {
            futuro.cancel(true);
            return null;
        } catch (Exception e) {
            return -1;
        } finally {
            executor.shutdownNow();
        }
    }
}