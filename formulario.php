<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>
<body>
    <section class="contenedor">
    <form class="formulario" action="">
        <fieldset>
            <legend>Tus datos</legend>
            <div class="campo">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre">
            </div>
            <div class="campo">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido">
            </div>
            <div class="campo">
                <label for="mail">Email:</label>
                <input type="email" id="mail">
            </div>
            <div class="campo">
                <label for="texto">Comentario</label>
                <textarea rows="20" cols="20" id="texto"></textarea>
            </div>
            <div>
                <label for="select">Equipo</label>
                <select id="select">
                    <option disabled selected value="">-- Seleccione --</option>
                    <option value="">Boca</option>
                    <option value="">River</option>
                    <option value="">San Lorenzo</option>
                    <option value="">Racing</option>
                    <option value="">Independiente</option>
                    <option value="">Newells</option>
                    <option value="">Rosario Central</option>
                </select>
            </div>
        </fieldset>
    </form>
    </section>
</body>
</html>