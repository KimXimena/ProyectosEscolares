import java.util.ArrayList;
import java.util.List;

public class Pregunta {
    private int idPregunta;
    private String textoPregunta;
    private String imagen;
    private List<Opcion> opciones;

    public Pregunta(int idPregunta, String textoPregunta, String imagen) {
        this.idPregunta = idPregunta;
        this.textoPregunta = textoPregunta;
        this.imagen = imagen;
        this.opciones = new ArrayList<>();
    }

    public int getIdPregunta() { return idPregunta; }
    public String getTextoPregunta() { return textoPregunta; }
    public String getImagen() { return imagen; }
    public List<Opcion> getOpciones() { return opciones; }

    public void agregarOpcion(Opcion opcion) {
        opciones.add(opcion);
    }
}