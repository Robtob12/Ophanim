const container = document.getElementById("newColumns");

// ➕ agregar columna
document.getElementById("addColumn").addEventListener("click", () => {

    const div = document.createElement("div");
    div.classList.add("column", "new-col");

    div.innerHTML = `
        <input type="text" name="new_name[]" placeholder="nombre">
        <input type="text" name="new_type[]" placeholder="tipo">

        <button type="button" class="removeColumn">❌</button>
    `;

    container.appendChild(div);
});

// ❌ eliminar columna (event delegation)
container.addEventListener("click", (e) => {
    if (e.target.classList.contains("removeColumn")) {
        e.target.parentElement.remove();
    }
});