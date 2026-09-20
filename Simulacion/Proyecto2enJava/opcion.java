public class Opcion {
    private int idOpcion;
    private String textoOpcion;
    private boolean correcta;

    public Opcion(int idOpcion, String textoOpcion, boolean correcta) {
        this.idOpcion = idOpcion;
        this.textoOpcion = textoOpcion;
        this.correcta = correcta;
    }

    public int getIdOpcion() { return idOpcion; }
    public String getTextoOpcion() { return textoOpcion; }
    public boolean isCorrecta() { return correcta; }
}