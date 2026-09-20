#include <stdio.h>
#include <stdlib.h>
#include <fcntl.h>
#include <sys/mman.h>
#include <sys/stat.h>
#include <unistd.h>
#include <semaphore.h>
#include <sys/wait.h>
#include <string.h>
#include <errno.h>

/*
 * Tarea: Memoria compartida + 1 semáforo + 2 procesos + 2 for
 * Padre: inicializa x=10, recorre 4 iteraciones, imprime resultados.
 * Hijo: en cada iteración hace: +5, -3, *2, /2 (si x != 0), sobre el valor x.
 * Sincronización: 1 semáforo POSIX nombrado, alternando sem_post/sem_wait.
 */

#define SHM_NAME "t4"
#define SEM_NAME "t4"

typedef struct {
    double x;        // valor actual se va encadenando
    double resultado;   // último resultado por el hijo
    int operaciones;    // operaciones
} shm_block;

static void die(const char *msg) {
    perror(msg);
    exit(EXIT_FAILURE);
}

int main(int argc, char *argv[]) {
    double x0 = 10.0; // valor iniciaL

    // Crear memoria compartida 
    int fd = shm_open(SHM_NAME, O_CREAT | O_RDWR, 0666);
    if (fd == -1) die("shm_open");
    if (ftruncate(fd, sizeof(shm_block)) == -1) die("ftruncate");
    shm_block *shm = (shm_block *)mmap(NULL, sizeof(shm_block),
                                       PROT_READ | PROT_WRITE, MAP_SHARED, fd, 0);
    if (shm == MAP_FAILED) die("mmap");
    close(fd); // el descriptor se puede cerrar tras mapear

    // abrir semáforo nombrado con valor inicial 0
    sem_t *sem = sem_open(SEM_NAME, O_CREAT, 0666, 0);
    if (sem == SEM_FAILED) die("sem_open");

    // inicia el bloque compartido
    shm->x = x0;
    shm->result = x0;
    shm->op_index = 0;

    pid_t pid = fork();
    if (pid < 0) die("fork");

    if (pid == 0) {
        // Proceso hijo
        for (int i = 0; i < 4; ++i) {
            // Espera la señal del padre para la iteración 
            if (sem_wait(sem) == -1) die("child sem_wait");

            int op = shm->op_index;
            double x = shm->x;
            double y = x;

            switch (op) {
                case 0: 
                    y = x + 5.0;
                    break;
                case 1: 
                    y = x - 3.0;
                    break;
                case 2:
                    y = x * 2.0;
                    break;
                case 3:
                    if (x != 0.0) y = x / 2.0;
                    else y = x;
                    break;
                default:
                    break;
            }
            shm->result = y;
            shm->x = y; // encadena el valor para la siguiente operación

            if (sem_post(sem) == -1) die("child sem_post");
        }
        if (munmap(shm, sizeof(shm_block)) == -1) die("child munmap");
        if (sem_close(sem) == -1) die("child sem_close");
        _exit(0);
    } else {
        // Proceso padre
        const char *op_names[4] = {"Suma x + 5", "Resta x - 3", "Multiplicación x * 2", "División x / 2"};

        printf("Valor inicial x0 = %.2f\n", x0);
        for (int i = 0; i < 4; ++i) {
            // Indica operación a realizar al hijo
            shm->op_index = i;
            if (sem_post(sem) == -1) die("parent sem_post");
            if (sem_wait(sem) == -1) die("parent sem_wait");

            printf("Iteración %d - %s: resultado = %.2f\n",
                   i + 1, op_names[i], shm->result);
        }

        int status = 0;
        waitpid(pid, &status, 0);

        // Limpieza en el padre
        if (munmap(shm, sizeof(shm_block)) == -1) die("parent munmap");
        if (shm_unlink(SHM_NAME) == -1) die("shm_unlink");
        if (sem_close(sem) == -1) die("parent sem_close");
        if (sem_unlink(SEM_NAME) == -1) die("sem_unlink");

        printf("Listo. Recursos liberados.\n");
    }

    return 0;
}
