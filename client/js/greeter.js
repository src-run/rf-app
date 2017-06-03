class Greeter {
    constructor(salutation = 'Hello') {
        this.salutation = salutation;
    }

    greet(name = 'World') {
        const greeting = `${this.salutation}, ${name}!`;
        console.log(greeting);
    }
}

export default Greeter;
