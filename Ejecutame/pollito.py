import turtle

def draw_heart(t, x, y, size, color):
    t.penup()
    t.goto(x, y)
    t.setheading(0)
    t.fillcolor(color)
    t.pencolor(color)
    t.pendown()
    t.begin_fill()
    t.left(140)
    t.forward(size)
    t.circle(-size / 2, 200)
    t.setheading(60)
    t.circle(-size / 2, 200)
    t.forward(size)
    t.end_fill()


def main():
    screen = turtle.Screen()
    screen.setup(width=600, height=600)
    screen.bgcolor("#FFF5E6")
    screen.title("Pollito ")

    # Desactivar la animación para que se dibuje al instante
    screen.tracer(3)

    t = turtle.Turtle()
    t.hideturtle()

    hearts = [
        (-180, 160, 30, "#FF6B81"),
        (150, 190, 25, "#FF4757"),
        (-160, -80, 20, "#FF6B81"),
        (160, -60, 35, "#FF4757"),
        (0, 220, 40, "#FF4757"),
    ]
    for x, y, size, color in hearts:
        draw_heart(t, x, y, size, color)

    # patitas
    t.pensize(4)
    t.pencolor("#FF9F43")

    # izquierda
    t.penup()
    t.goto(-20, -80)
    t.pendown()
    t.setheading(-90)
    t.forward(35)
    t.left(30)
    t.forward(15)
    t.backward(15)
    t.right(60)
    t.forward(15)

    # derecha
    t.penup()
    t.goto(20, -80)
    t.setheading(-90)
    t.pendown()
    t.forward(35)
    t.left(30)
    t.forward(15)
    t.backward(15)
    t.right(60)
    t.forward(15)

    # cuerpo
    t.pensize(2)
    t.pencolor("#F1C40F")
    t.fillcolor("#FECA57")

    t.penup()
    t.goto(0, -80)
    t.setheading(0)
    t.pendown()
    t.begin_fill()
    t.circle(80)
    t.end_fill()

    # alas
    # izquierda
    t.penup()
    t.goto(-70, 0)
    t.setheading(200)
    t.pendown()
    t.begin_fill()
    t.circle(35, 120)
    t.goto(-70, 0)
    t.end_fill()

    # derecha
    t.penup()
    t.goto(70, 0)
    t.setheading(-20)
    t.pendown()
    t.begin_fill()
    t.circle(-35, 120)
    t.goto(70, 0)
    t.end_fill()

    # mejillas
    t.pencolor("#FF9FF3")
    t.fillcolor("#FF9FF3")

    t.penup()
    t.goto(-40, 10)
    t.pendown()
    t.begin_fill()
    t.circle(10)
    t.end_fill()

    t.penup()
    t.goto(40, 10)
    t.pendown()
    t.begin_fill()
    t.circle(10)
    t.end_fill()

    # ojos
    t.pencolor("#2C3E50")
    t.fillcolor("#2C3E50")

    # izquierdo
    t.penup()
    t.goto(-25, 30)
    t.pendown()
    t.begin_fill()
    t.circle(8)
    t.end_fill()

    # Brillo izquierdo
    t.pencolor("white")
    t.fillcolor("white")
    t.penup()
    t.goto(-23, 37)
    t.pendown()
    t.begin_fill()
    t.circle(3)
    t.end_fill()

    # derecho
    t.pencolor("#2C3E50")
    t.fillcolor("#2C3E50")
    t.penup()
    t.goto(25, 30)
    t.pendown()
    t.begin_fill()
    t.circle(8)
    t.end_fill()

    # Brillo derecho
    t.pencolor("white")
    t.fillcolor("white")
    t.penup()
    t.goto(27, 37)
    t.pendown()
    t.begin_fill()
    t.circle(3)
    t.end_fill()

    # pico
    t.pencolor("#FF9F43")
    t.fillcolor("#FF9F43")
    t.penup()
    t.goto(-12, 20)
    t.setheading(0)
    t.pendown()
    t.begin_fill()
    t.goto(12, 20)
    t.goto(0, 5)
    t.goto(-12, 20)
    t.end_fill()

    # pluma
    t.pencolor("#F1C40F")
    t.fillcolor("#FECA57")
    t.penup()
    t.goto(-8, 78)
    t.setheading(45)
    t.pendown()
    t.begin_fill()
    t.circle(12, 180)
    t.end_fill()

    # texto
    t.penup()
    t.goto(0, -220)
    t.pencolor("#FF4757")
    t.write(
        "¡Te quiero mucho mucho! ❤️",
        align="center",
        font=("Comic Sans MS", 22, "bold"),
    )

    # Actualizar la pantalla con todo el dibujo ya terminado
    screen.update()
    screen.mainloop()


if __name__ == "__main__":
    main()