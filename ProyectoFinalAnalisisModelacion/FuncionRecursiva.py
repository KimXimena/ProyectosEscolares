def f(n: int) -> int:
    #validacion de entrada
    if not isinstance(n, int):
        raise TypeError("n debe ser un entero")
    #validcion de no negativo
    if n < 0:
        raise ValueError("n debe ser un entero no negativo")
    #condidicion de parada
    if n in (0,1):
        return 1
    #caso recursivo
    return n * f(n-1)

print(f(5))
print(f(10))