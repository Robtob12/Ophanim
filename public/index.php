<?php
/*
session_start();
session_destroy();
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ophanim DDB</title>
    <link rel="stylesheet" href="styles/index.css">
</head>
<body>
    <nav>
        <a class="icon" href="index.php">
            <h2>< Ophanim ></h2>
        </a>

        <div class="links">
            <a href="">Documentacion</a>
            <a href="#herramientas">Herramientas</a>
            <a href="">Ajuda</a>
            <div class="capsula">
                <a class="a_capsula" href="https://github.com/Robtob12/Ophanim">GitHub</a>
                <a class="a_capsula" href="">LinkedIn</a>
            </div>
            <div class="login">
                <a href="register.php">Entrar</a>
            </div>
        </div>
    </nav>
    <header>
        <div class="box1">
            <h1>Una herramienta que facilita el trabajo para todos</h1>
            <p>
                Ophanim es una herramienta de gestión de bases de datos dinámica 
                diseñada para simplificar la forma en que creas, organizas y administras 
                tu información. Pensada tanto para desarrolladores como para equipos 
                de trabajo, te permite estructurar, conectar y gestionar tu propio 
                sistema de datos directamente desde la web, sin complicaciones.
            </p>

            <p>
                Con Ophanim puedes crear modelos personalizados, automatizar procesos, 
                visualizar relaciones entre datos y mantener todo centralizado en un 
                entorno seguro y accesible. Ya sea para proyectos personales o soluciones 
                empresariales, Ophanim te ayuda a ahorrar tiempo y mejorar la eficiencia.
            </p>

            <p>
                Empieza a construir y controlar tus datos de forma inteligente, con una 
                interfaz intuitiva y herramientas potentes que se adaptan a tus necesidades.
            </p>

            <div class="divisor">
                <a class="btn1" href="">
                    <span class="hoverLink"> < </span> Probar ahora! <span class="hoverLink"> > </span>
                </a>
                <a class="btn2" href="">
                    <span class="hoverLink"> < </span> Hacer login <span class="hoverLink"> > </span>
                </a>
            </div>
        </div>

        <div class="box2">
            <img src="image/database.jpg" alt="Sistema de base de datos Ophanim">
        </div>
    </header>
    <section id="herramientas">
        <h2>Herramientas de Ophanim</h2>
        <p class="subtitle">
            Descubre algunas de las funcionalidades que hacen de Ophanim una plataforma poderosa y fácil de usar.
        </p>

        <div class="tools-container">

            <div class="tool">
                <h3>Creación de bases de datos sin código</h3>
                <p>
                    Diseña y construye tu base de datos sin necesidad de programar. 
                    A través de una interfaz intuitiva, puedes crear tablas, relaciones 
                    y estructuras completas con simples configuraciones manuales, ideal 
                    para usuarios sin experiencia técnica.
                </p>
            </div>

            <div class="tool">
                <h3>Conexión a bases de datos externas</h3>
                <p>
                    Conecta fácilmente bases de datos existentes dentro de tu red local 
                    mediante IP o conexión WiFi. Ophanim permite integrar sistemas ya 
                    creados sin necesidad de migraciones complejas.
                </p>
            </div>

            <div class="tool">
                <h3>Importación y visualización de modelos</h3>
                <p>
                    Carga modelos de bases de datos previamente creados y visualízalos 
                    en una interfaz gráfica clara. Además, puedes modificar estructuras, 
                    relaciones y campos directamente desde la plataforma.
                </p>
            </div>

            <div class="tool">
                <h3>Gestión y edición en tiempo real</h3>
                <p>
                    Administra tus datos de forma dinámica con cambios en tiempo real. 
                    Inserta, edita o elimina información de manera rápida y segura, 
                    manteniendo siempre el control total de tu sistema.
                </p>
            </div>

        </div>
    </section>
    <footer class="footer">
        <div class="footer-container">

            <div class="footer-brand">
                <h2>< Ophanim ></h2>
                <p>
                    Plataforma moderna para la gestión de bases de datos 
                    de forma simple, visual y eficiente.
                </p>
            </div>

            <div class="footer-links">
                <h3>Enlaces</h3>
                <a href="#">Inicio</a>
                <a href="#">Herramientas</a>
                <a href="#">Documentación</a>
                <a href="#">Contacto</a>
            </div>

            <div class="footer-links">
                <h3>Cuenta</h3>
                <a href="#">Login</a>
                <a href="#">Registro</a>
                <a href="#">Soporte</a>
            </div>

            <div class="footer-social">
                <h3>Redes</h3>
                <a href="#">GitHub</a>
                <a href="#">LinkedIn</a>
                <a href="#">Twitter</a>
            </div>

        </div>

        <div class="footer-bottom">
            <p>© 2026 Ophanim. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>