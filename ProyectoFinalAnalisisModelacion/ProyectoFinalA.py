import random
import copy
import time
import blosum
import matplotlib.pyplot as plt

# funciones originales
blosum62 = blosum.BLOSUM(62)
NFE = 0   # Número de evaluaciones de función

def get_sequences():
    seq1 = "MGSSHHHHHHSSGLVPRGSHMASMTGGQQMGRDLYDDDDKDRWGKLVVLGAVTQGQKLVVLGAGGVGKSALTIQLIQNHFVDEYDPTIEDSYRKQVVIDGGGVGKSALTIQLIQNHFVDEYDPTIEDSYRKQV"
    seq2 = "MKTLLVAAAVVAGGQGQAEKLVKQLEQKAKELQKQLEQKAKELQKQLEQKAKELQKQLEQKAKELQKQLEQKAGVGKSALTIQLIQNHFVDEYDPTIEDSYRKQVVIDGETCLLDILDTAGQEEYSAMRDQKELQKQLGQKAKEL"
    seq3 = "MAVTQGQKLVVLGAGGVGKSALTIQLIQNHFVDEYDPTIEDSYRKQVVIDGETCLLDILDTAGQEEYSAMRDQYMRTGEGFAVVAGGQGQAEKLVKQLEQKAKELQKQLEQKAKELQKQLEQKAKELQKQLEQKAKELQKQLEQKALCVFAIN"
    return [list(seq1), list(seq2), list(seq3)]

def crear_individuo():
    return get_sequences()

def crear_poblacion_inicial(n=10):
    individuo_base = crear_individuo()
    poblacion = [[row[:] for row in individuo_base] for _ in range(n)]
    return poblacion

def mutar_poblacion_v2(poblacion, num_gaps=1):
    poblacion_mutada = []
    for individuo in poblacion:
        nuevo_individuo = []
        for fila in individuo:
            fila_mutada = fila[:]
            posiciones = set()
            for _ in range(num_gaps):
                pos = random.randint(0, len(fila_mutada))
                while pos in posiciones:
                    pos = random.randint(0, len(fila_mutada))
                posiciones.add(pos)
                fila_mutada.insert(pos, '-')
            nuevo_individuo.append(fila_mutada)
        poblacion_mutada.append(nuevo_individuo)
    return poblacion_mutada

def igualar_longitud_secuencias(individuo, gap='-'):
    max_len = max(len(fila) for fila in individuo)
    individuo_igualado = [fila + [gap]*(max_len - len(fila)) for fila in individuo]
    return individuo_igualado

def evaluar_individuo_blosum62(individuo):
    global NFE
    NFE += 1
    score = 0
    n_seqs = len(individuo)
    seq_len = len(individuo[0])
    for col in range(seq_len):
        for i in range(n_seqs):
            for j in range(i+1, n_seqs):
                a = individuo[i][col]
                b = individuo[j][col]
                if a == '-' or b == '-':
                    score -= 4
                else:
                    score += blosum62[a][b]
    return score

def eliminar_peores(poblacion, scores, porcentaje=0.5):
    idx_ordenados = sorted(range(len(scores)), key=lambda i: scores[i], reverse=True)
    n_seleccionados = int(len(poblacion) * porcentaje)

    ind_seleccionados = [poblacion[i] for i in idx_ordenados[:n_seleccionados]]
    scores_seleccionados = [scores[i] for i in idx_ordenados[:n_seleccionados]]

    return ind_seleccionados, scores_seleccionados

def cruzar_individuos_doble_punto(ind1, ind2):
    hijo1 = []
    hijo2 = []
    for seq1, seq2 in zip(ind1, ind2):
        aa_indices = [i for i, a in enumerate(seq1) if a != '-']
        if len(aa_indices) < 6:
            hijo1.append(seq1[:])
            hijo2.append(seq2[:])
            continue

        # Forzar segmento mínimo de 5 aa
        intentos = 0
        while True:
            p1, p2 = sorted(random.sample(aa_indices, 2))
            if p2 - p1 >= 5 or intentos > 10:
                break
            intentos += 1

        def cruza(seqA, seqB):
            aaA = [a for a in seqA if a != '-']
            aaB = [a for a in seqB if a != '-']
            nueva = aaA[:p1] + aaB[p1:p2] + aaA[p2:]
            resultado = []
            idx = 0
            for a in seqA:
                if a == '-':
                    resultado.append('-')
                else:
                    resultado.append(nueva[idx])
                    idx += 1
            return resultado

        nueva_seq1 = cruza(seq1, seq2)
        nueva_seq2 = cruza(seq2, seq1)

        hijo1.append(nueva_seq1)
        hijo2.append(nueva_seq2)

    # mutación original fija
    hijo1 = mutar_individuo(hijo1, 1, 0.8)
    hijo2 = mutar_individuo(hijo2, 1, 0.8)
    return hijo1, hijo2

def mutar_individuo(individuo, n_gaps, p):
    nuevo_individuo = []
    for secuencia in individuo:
        sec = secuencia[:]
        if random.random() < p:
            posiciones = set()
            for _ in range(n_gaps):
                pos = random.randint(0, len(sec))
                while pos in posiciones:
                    pos = random.randint(0, len(sec))
                posiciones.add(pos)
                sec.insert(pos, '-')
        nuevo_individuo.append(sec)
    return nuevo_individuo

def cruzar_poblacion_doble_punto(poblacion):
    nueva_poblacion = []
    n = len(poblacion)
    indices = list(range(n))
    random.shuffle(indices)
    parejas = [(indices[i], indices[i+1]) for i in range(0, n-1, 2)]
    if n % 2 == 1:
        parejas.append((indices[-1], indices[0]))
    for idx1, idx2 in parejas:
        padre1 = poblacion[idx1]
        padre2 = poblacion[idx2]
        hijo1, hijo2 = cruzar_individuos_doble_punto(padre1, padre2)

        nueva_poblacion.append(copy.deepcopy(padre1))
        nueva_poblacion.append(copy.deepcopy(padre2))
        nueva_poblacion.append(hijo1)
        nueva_poblacion.append(hijo2)
    return nueva_poblacion[:2*n]

def validar_poblacion_sin_gaps(poblacion, originales):
    for individuo in poblacion:
        for seq, seq_orig in zip(individuo, originales):
            seq_sin_gaps = [a for a in seq if a != '-']
            seq_orig_sin_gaps = [a for a in seq_orig if a != '-']
            if seq_sin_gaps != seq_orig_sin_gaps:
                return False
    return True

def obtener_best(scores, poblacion):
    idx_mejor = scores.index(max(scores))
    fitness_best = scores[idx_mejor]
    best = copy.deepcopy(poblacion[idx_mejor])
    return best, fitness_best

#  MEJORAS: Elitismo, Selección por torneo, Mutación adaptativa

def torneo_seleccion(poblacion, scores, k=3):
    
    #Devuelve el índice del mejor individuo entre k tomados al azar.
    
    indices = random.sample(range(len(poblacion)), k)
    mejor = max(indices, key=lambda i: scores[i])
    return mejor

def cruzar_individuos_doble_punto_sin_mutacion(ind1, ind2):

    #Versión de cruza SIN mutación interna, para controlar luego p_mut.
    hijo1 = []
    hijo2 = []
    for seq1, seq2 in zip(ind1, ind2):
        aa_indices = [i for i, a in enumerate(seq1) if a != '-']
        if len(aa_indices) < 6:
            hijo1.append(seq1[:])
            hijo2.append(seq2[:])
            continue

        intentos = 0
        while True:
            p1, p2 = sorted(random.sample(aa_indices, 2))
            if p2 - p1 >= 5 or intentos > 10:
                break
            intentos += 1

        def cruza(seqA, seqB):
            aaA = [a for a in seqA if a != '-']
            aaB = [a for a in seqB if a != '-']
            nueva = aaA[:p1] + aaB[p1:p2] + aaA[p2:]
            resultado = []
            idx = 0
            for a in seqA:
                if a == '-':
                    resultado.append('-')
                else:
                    resultado.append(nueva[idx])
                    idx += 1
            return resultado

        nueva_seq1 = cruza(seq1, seq2)
        nueva_seq2 = cruza(seq2, seq1)

        hijo1.append(nueva_seq1)
        hijo2.append(nueva_seq2)

    return hijo1, hijo2

def cruzar_y_mutar_mejorado(padre1, padre2, p_mut, n_gaps=1):
      #Cruza de doble punto usando sólo aminoácidos y mutación con p_mut adaptativa.
    
    h1, h2 = cruzar_individuos_doble_punto_sin_mutacion(padre1, padre2)
    h1 = mutar_individuo(h1, n_gaps, p_mut)
    h2 = mutar_individuo(h2, n_gaps, p_mut)
    return h1, h2

# ALGORITMO ORIGINAL

def ejecutar_algoritmo_original(num_generaciones=100, tam_poblacion=10):
    global NFE
    NFE = 0
    start_time = time.time()

    veryBest = None
    fitnessVeryBest = None
    historial_best = []

    poblacion = crear_poblacion_inicial(tam_poblacion)
    poblacion = mutar_poblacion_v2(poblacion, num_gaps=1)
    poblacion = [igualar_longitud_secuencias(ind) for ind in poblacion]
    scores = [evaluar_individuo_blosum62(ind) for ind in poblacion]
    poblacion, scores = eliminar_peores(poblacion, scores)

    for gen in range(num_generaciones):
        poblacion = cruzar_poblacion_doble_punto(poblacion)
        poblacion = [igualar_longitud_secuencias(ind) for ind in poblacion]
        scores = [evaluar_individuo_blosum62(ind) for ind in poblacion]
        poblacion, scores = eliminar_peores(poblacion, scores)

        best, fitness_best = obtener_best(scores, poblacion)
        historial_best.append(fitness_best)

        if veryBest is None or fitness_best > fitnessVeryBest:
            veryBest = best
            fitnessVeryBest = fitness_best

        elapsed = time.time() - start_time
        # print(f"[ORIGINAL] Gen {gen} - fitness: {fitnessVeryBest} NFE: {NFE} time: {elapsed:.2f}s")

    integridad = validar_poblacion_sin_gaps(poblacion, get_sequences())
    return veryBest, fitnessVeryBest, historial_best, NFE, elapsed, integridad

#ALGORITMO MEJORADO

def ejecutar_algoritmo_mejorado(
    num_generaciones=100,
    tam_poblacion=20,
    elite_size=2,
    p_mut_inicio=0.8,
    p_mut_min=0.2,
    torneo_k=3
):

    #Mejoras:
    #Población más grande.
    #Elitismo: los mejores 'elite_size' pasan directo a la siguiente generación.
    #Selección por torneo, en lugar de sólo truncar peores
    #Mutación adaptativa: alta al inicio, baja al final
    global NFE
    NFE = 0
    start_time = time.time()

    veryBest = None
    fitnessVeryBest = None
    historial_best = []

    # población inicial
    poblacion = crear_poblacion_inicial(tam_poblacion)
    poblacion = mutar_poblacion_v2(poblacion, num_gaps=1)
    poblacion = [igualar_longitud_secuencias(ind) for ind in poblacion]

    for gen in range(num_generaciones):
        scores = [evaluar_individuo_blosum62(ind) for ind in poblacion]

        # ranking
        idx_ordenados = sorted(range(len(scores)), key=lambda i: scores[i], reverse=True)

        # elites
        elites = [copy.deepcopy(poblacion[i]) for i in idx_ordenados[:elite_size]]

        best_gen = scores[idx_ordenados[0]]
        historial_best.append(best_gen)

        # actualizar super-mejor
        if veryBest is None or best_gen > fitnessVeryBest:
            veryBest = copy.deepcopy(poblacion[idx_ordenados[0]])
            fitnessVeryBest = best_gen

        # mutación adaptativa
        t = gen / float(num_generaciones - 1 if num_generaciones > 1 else 1)
        p_mut = p_mut_inicio - (p_mut_inicio - p_mut_min) * t

        nueva_poblacion = elites[:]

        # generar el resto por torneo y cruza doble punto
        while len(nueva_poblacion) < tam_poblacion:
            i1 = torneo_seleccion(poblacion, scores, k=torneo_k)
            i2 = torneo_seleccion(poblacion, scores, k=torneo_k)
            padre1 = poblacion[i1]
            padre2 = poblacion[i2]

            h1, h2 = cruzar_y_mutar_mejorado(padre1, padre2, p_mut, n_gaps=1)

            nueva_poblacion.append(h1)
            if len(nueva_poblacion) < tam_poblacion:
                nueva_poblacion.append(h2)

        # igualar longitudes para mantener matriz rectangular
        poblacion = [igualar_longitud_secuencias(ind) for ind in nueva_poblacion]

        elapsed = time.time() - start_time
        # print(f"[MEJORADO] Gen {gen} - best_gen: {best_gen} p_mut: {p_mut:.2f} NFE: {NFE} time: {elapsed:.2f}s")

    integridad = validar_poblacion_sin_gaps(poblacion, get_sequences())
    return veryBest, fitnessVeryBest, historial_best, NFE, elapsed, integridad

# COMPARACIÓN Y GRÁFICA

if __name__ == "__main__":
    #random.seed(42) 

    # Algoritmo original
    best_o, fit_o, hist_o, nfe_o, tiempo_o, integ_o = ejecutar_algoritmo_original(
        num_generaciones=100,
        tam_poblacion=10
    )

    print("RESULTADOS ALGORITMO ORIGINAL ")
    print("Mejor fitness:", fit_o)
    print("NFE:", nfe_o)
    print("Tiempo (s):", tiempo_o)
    print("Validación de integridad:", integ_o)
    print()

    # Algoritmo mejorado 
    best_m, fit_m, hist_m, nfe_m, tiempo_m, integ_m = ejecutar_algoritmo_mejorado(
        num_generaciones=100,
        tam_poblacion=20,
        elite_size=2,
        p_mut_inicio=0.8,
        p_mut_min=0.2,
        torneo_k=3
    )

    print("RESULTADOS ALGORITMO MEJORADO")
    print("Mejor fitness:", fit_m)
    print("NFE:", nfe_m)
    print("Tiempo (s):", tiempo_m)
    print("Validación de integridad:", integ_m)
    print()

    # Gráfica de comparación
    gens_o = list(range(len(hist_o)))
    gens_m = list(range(len(hist_m)))

    plt.figure()
    plt.plot(gens_o, hist_o, label="Original")
    plt.plot(gens_m, hist_m, label="Mejorado")
    plt.xlabel("Generación")
    plt.ylabel("Mejor fitness")
    plt.title("Comparación Algoritmo Genético: Original vs Mejorado")
    plt.legend()
    plt.grid(True)
    plt.tight_layout()
    plt.savefig("comparacion_fitness.png", dpi=150)
    plt.show()
