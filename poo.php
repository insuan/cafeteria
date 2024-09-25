<?php

class Post{
    public string $nombre = "Nicolas";

    public function nombre($dato){

        return "Mi nombre es $dato";
    }
}

$miObjeto = new Post; // nueva instancia u objeto de la clase post

echo $miObjeto->nombre;
echo $miObjeto->nombre("Juan");

?>