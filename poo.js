class Cliente {
    constructor(nombre,apellido){ //atributos del constructor
        this.nombre = nombre;
        this.apellido = apellido;
    }

    detalles(){ //metodos
        return `Su nombre es ${this.nombre} y su apellido es ${this.apellido}`;
    }
};


const persona = new Cliente("Nicolas","Insua");
const persona2 = new Cliente("Diego","Insua");

console.log(persona);
console.log(persona.detalles());

class Empresa extends Cliente{
    constructor(nombre,apellido){
        super(nombre,apellido);
    }

}

const prueba = new Empresa("Empresa","empresa");

console.log(prueba);
