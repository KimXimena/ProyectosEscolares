import java.sql.*;

public class UsuarioDAO {

    public boolean registrarUsuario(String nombre, String correo, String contrasena) {
        String sql = "INSERT INTO Usuarios(nombre, correo, `contraseña`) VALUES (?, ?, ?)";

        try (Connection conn = ConexionBD.conectar();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, nombre);
            ps.setString(2, correo);
            ps.setString(3, contrasena);

            return ps.executeUpdate() > 0;

        } catch (SQLIntegrityConstraintViolationException e) {
            System.out.println("Ese correo ya está registrado.");
        } catch (SQLException e) {
            System.out.println("Error al registrar usuario: " + e.getMessage());
        }
        return false;
    }

    public Usuario login(String correo, String contrasena) {
        String sql = "SELECT * FROM Usuarios WHERE correo = ? AND `contraseña` = ?";

        try (Connection conn = ConexionBD.conectar();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setString(1, correo);
            ps.setString(2, contrasena);

            ResultSet rs = ps.executeQuery();

            if (rs.next()) {
                return new Usuario(
                        rs.getInt("id_usuario"),
                        rs.getString("nombre"),
                        rs.getString("correo"),
                        rs.getString("contraseña"),
                        rs.getInt("intentos_simulador"),
                        rs.getInt("intentos_final")
                );
            }

        } catch (SQLException e) {
            System.out.println("Error en login: " + e.getMessage());
        }

        return null;
    }

    public void incrementarIntentoSimulador(int idUsuario) {
        String sql = "UPDATE Usuarios SET intentos_simulador = intentos_simulador + 1 WHERE id_usuario = ?";

        try (Connection conn = ConexionBD.conectar();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, idUsuario);
            ps.executeUpdate();

        } catch (SQLException e) {
            System.out.println("Error al actualizar intentos simulador: " + e.getMessage());
        }
    }

    public void incrementarIntentoFinal(int idUsuario) {
        String sql = "UPDATE Usuarios SET intentos_final = intentos_final + 1 WHERE id_usuario = ?";

        try (Connection conn = ConexionBD.conectar();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, idUsuario);
            ps.executeUpdate();

        } catch (SQLException e) {
            System.out.println("Error al actualizar intentos final: " + e.getMessage());
        }
    }

    public Usuario obtenerUsuarioPorId(int idUsuario) {
        String sql = "SELECT * FROM Usuarios WHERE id_usuario = ?";

        try (Connection conn = ConexionBD.conectar();
             PreparedStatement ps = conn.prepareStatement(sql)) {

            ps.setInt(1, idUsuario);
            ResultSet rs = ps.executeQuery();

            if (rs.next()) {
                return new Usuario(
                        rs.getInt("id_usuario"),
                        rs.getString("nombre"),
                        rs.getString("correo"),
                        rs.getString("contraseña"),
                        rs.getInt("intentos_simulador"),
                        rs.getInt("intentos_final")
                );
            }

        } catch (SQLException e) {
            System.out.println("Error al obtener usuario: " + e.getMessage());
        }

        return null;
    }
}