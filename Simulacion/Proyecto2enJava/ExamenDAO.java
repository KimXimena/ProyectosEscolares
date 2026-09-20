import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ExamenDAO {

    public List<Pregunta> obtenerPreguntasAleatorias(int cantidad) {
        List<Pregunta> preguntas = new ArrayList<>();

        String sqlPreguntas = "SELECT * FROM Preguntas ORDER BY RAND() LIMIT ?";

        try (Connection conn = ConexionBD.conectar();
             PreparedStatement ps = conn.prepareStatement(sqlPreguntas)) {

            ps.setInt(1, cantidad);
            ResultSet rs = ps.executeQuery();

            while (rs.next()) {
                int idPregunta = rs.getInt("id_pregunta");
                String texto = rs.getString("pregunta");
                String imagen = rs.getString("imagen");

                Pregunta pregunta = new Pregunta(idPregunta, texto, imagen);
                cargarOpciones(conn, pregunta);
                preguntas.add(pregunta);
            }

        } catch (SQLException e) {
            System.out.println("Error al obtener preguntas: " + e.getMessage());
        }

        return preguntas;
    }

    private void cargarOpciones(Connection conn, Pregunta pregunta) throws SQLException {
        String sqlOpciones = "SELECT * FROM Opciones WHERE id_pregunta = ? ORDER BY RAND()";

        try (PreparedStatement ps = conn.prepareStatement(sqlOpciones)) {
            ps.setInt(1, pregunta.getIdPregunta());
            ResultSet rs = ps.executeQuery();

            while (rs.next()) {
                Opcion opcion = new Opcion(
                        rs.getInt("id_opcion"),
                        rs.getString("texto_opcion"),
                        rs.getBoolean("es_correcta")
                );
                pregunta.agregarOpcion(opcion);
            }
        }
    }
}