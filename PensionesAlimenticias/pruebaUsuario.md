
````mermaid
flowchart LR

    %% ==== CARRIL USUARIO ====
    subgraph USUARIO
        U_A(("Inicio"))
        U_B["Recibir oficio del juzgado"]
        U_CAPT_ALTA["Capturar alta en SIAPSEP"]
        U_JM_OFICIO["Recibir oficio de juicio mercantil"]
    end

    %% ==== CARRIL SISTEMA ====
    subgraph SISTEMA
        S_C["Leer datos del oficio"]
        S_D2["Investigar trabajador en nómina"]
        S_D3["Calcular retroactivo"]
        S_D4["Revisar liquidez del trabajador"]
        S_D6["Iniciar pago por cheque"]
        S_D7["Digitalizar oficio"]

        S_E1["Procesar cambio de pensión"]
        S_F1{"¿Sostenimiento?"}
        S_F2["Reintegro federal"]
        S_F3["Reintegro estatal"]
        S_F4["Registrar en bitácora"]
        S_F5["Digitalizar oficio de reintegro"]

        S_G1["Recibir oficio de baja"]
        S_G2["Cerrar efectos en SIAPSEP"]
        S_G3["Digitalizar oficio de baja"]

        S_H2["Calcular monto 30%"]
        S_H3["Calcular número de quincenas"]
        S_H4["Capturar alta tipo M en SIAPSEP"]
        S_H5["Digitalizar oficio de juicio"]
        S_H6["Aplicar descuento concepto 66 en nómina"]

        S_FIN(("Fin del proceso"))
    end

    %% ==== FLUJO PRINCIPAL ====
    U_A --> U_B --> S_C
    S_C --> S_D2 --> S_D3 --> S_D4 --> U_CAPT_ALTA --> S_D6 --> S_D7 --> S_FIN

    %% ==== RAMA: CAMBIO DE PENSIÓN ====
    S_C --> S_E1 --> S_D7

    %% ==== RAMA: REINTEGRO ====
    S_C --> S_F1
    S_F1 --> S_F2 --> S_F4
    S_F1 --> S_F3 --> S_F4
    S_F4 --> S_F5 --> S_FIN

    %% ==== RAMA: BAJA ====
    S_C --> S_G1 --> S_G2 --> S_G3 --> S_FIN

    %% ==== RAMA: JUICIO MERCANTIL ====
    U_JM_OFICIO --> S_H2 --> S_H3 --> S_H4 --> S_H5 --> S_H6 --> S_FIN
````