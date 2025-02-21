document.addEventListener("DOMContentLoaded", function () {
    // Get the category from the URL
    const params = new URLSearchParams(window.location.search);
    const category = params.get("category");

    // Set page title
    document.getElementById("category-title").innerText = category ? category : "All Products";

    // Sample product data
    const products = {
        "String Instruments": [
            { name: "Acoustic Guitar", image: "assets/images/products/guitar.jpg" },
            { name: "Violin", image: "assets/images/products/violin.jpg" }
        ],
        "Percussion Instruments": [
            { name: "Drum Set", image: "assets/images/products/drums.jpg" },
            { name: "Cajón", image: "assets/images/products/cajon.jpg" }
        ]
    };

    // Get the product container
    const productGrid = document.getElementById("product-grid");

    // Display relevant products
    if (products[category]) {
        products[category].forEach((product) => {
            const div = document.createElement("div");
            div.classList.add("product-box");
            div.innerHTML = `
                <img src="${product.image}" alt="${product.name}">
                <h3>${product.name}</h3>
            `;
            productGrid.appendChild(div);
        });
    } else {
        productGrid.innerHTML = "<p>No products found for this category.</p>";
    }
});

// Filter function for search bar
function filterProducts() {
    let input = document.getElementById("search-bar").value.toLowerCase();
    let products = document.querySelectorAll(".product-box");

    products.forEach((product) => {
        let name = product.querySelector("h3").innerText.toLowerCase();
        product.style.display = name.includes(input) ? "block" : "none";
    });
}
