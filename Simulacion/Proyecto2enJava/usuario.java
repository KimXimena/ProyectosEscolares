public class Usuario {
    private int idUsuario;
    private String nombre;
    private String correo;
    private String contrasena;
    private int intentosSimulador;
    private int intentosFinal;

    public Usuario() {}

    public Usuario(int idUsuario, String nombre, String correo, String contrasena, int intentosSimulador, int intentosFinal) {
        this.idUsuario = idUsuario;
        this.nombre = nombre;
        this.correo = correo;
        this.contrasena = contrasena;
        this.intentosSimulador = intentosSimulador;
        this.intentosFinal = intentosFinal;
    }

    public int getIdUsuario() { return idUsuario; }
    public void setIdUsuario(int idUsuario) { this.idUsuario = idUsuario; }

    public String getNombre() { return nombre; }
    public void setNombre(String nombre) { this.nombre = nombre; }

    public String getCorreo() { return correo; }
    public void setCorreo(String correo) { this.correo = correo; }

    public String getContrasena() { return contrasena; }
    public void setContrasena(String contrasena) { this.contrasena = contrasena; }

    public int getIntentosSimulador() { return intentosSimulador; }
    public void setIntentosSimulador(int intentosSimulador) { this.intentosSimulador = intentosSimulador; }

    public int getIntentosFinal() { return intentosFinal; }
    public void setIntentosFinal(int intentosFinal) { this.intentosFinal = intentosFinal; }
}