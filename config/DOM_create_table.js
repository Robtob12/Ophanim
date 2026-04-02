let columnIndex = 0;
let fkIndex = 0;

const columnsContainer = document.getElementById("columns");
const addColumnBtn = document.getElementById("addColumn");

const fkContainer = document.getElementById("fks");
const addFkBtn = document.getElementById("addFK");

/* 🔹 CREAR COLUMNA */
function createColumn() {
    const div = document.createElement("div");
    div.classList.add("column");

    div.innerHTML = `
        <input type="text" name="columns[${columnIndex}][name]" placeholder="nombre">

        <select name="columns[${columnIndex}][type]">
            <option value="INT">INT</option>
            <option value="VARCHAR">VARCHAR</option>
            <option value="TEXT">TEXT</option>
            <option value="DATE">DATE</option>
            <option value="DATETIME">DATETIME</option>
            <option value="BOOLEAN">BOOLEAN</option>
        </select>

        <input type="number" name="columns[${columnIndex}][length]" placeholder="len">

        <label><input type="checkbox" name="columns[${columnIndex}][null]">NULL</label>
        <label><input type="checkbox" name="columns[${columnIndex}][pk]">PK</label>
        <label><input type="checkbox" name="columns[${columnIndex}][ai]">AI</label>

        <input type="text" name="columns[${columnIndex}][default]" placeholder="default">

        <button type="button" class="removeColumn">X</button>
    `;

    columnsContainer.appendChild(div);
    columnIndex++;
}

/* 🔹 ELIMINAR COLUMNA */
columnsContainer.addEventListener("click", function(e){
    if(e.target.classList.contains("removeColumn")){
        e.target.parentElement.remove();
    }
});

/* 🔹 CREAR FK */
function createFK(){
    const div = document.createElement("div");
    div.classList.add("fk");

    div.innerHTML = `
        <input type="text" name="fk[${fkIndex}][column]" placeholder="columna_local">
        <input type="text" name="fk[${fkIndex}][ref_table]" placeholder="tabla_ref">
        <input type="text" name="fk[${fkIndex}][ref_column]" placeholder="columna_ref">

        <button type="button" class="removeFK">X</button>
    `;

    fkContainer.appendChild(div);
    fkIndex++;
}

/* 🔹 ELIMINAR FK */
fkContainer.addEventListener("click", function(e){
    if(e.target.classList.contains("removeFK")){
        e.target.parentElement.remove();
    }
});

/* 🔹 EVENTOS */
addColumnBtn.addEventListener("click", createColumn);
addFkBtn.addEventListener("click", createFK);

/* 🔥 INICIAL (IMPORTANTE) */
createColumn(); // siempre al menos 1 columna