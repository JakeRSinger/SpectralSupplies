let offset = 0;
let fetchComplete = false;
let isFetching = false;

window.addEventListener('load', fetchProducts);

async function fetchProducts() {
    if (fetchComplete) return;

    const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/products.php?offset=" + offset);
    const productList = await response.json();
    offset += 8;

    // Check if no data was returned, which means all products fetched
    if (productList.length === 0) fetchComplete = true;

    writeProducts(productList);
}

function writeProducts(productList) {
    const ssProducts = document.getElementById('products');  // Main product container

    // Loop through each product in the productList
    for (const product of productList) {
        const productDiv = document.createElement('div');
        productDiv.classList.add("product");

        const img = document.createElement('img');
        img.src = "https://comp-server.uhi.ac.uk/~21011375/ss-webservice/img/" + product.stock_id + ".png";
        img.alt = product.stock_name;

        const productDetailsDiv = document.createElement('div');
        productDetailsDiv.classList.add("item-details");

        const namePrice = document.createElement('div');
        namePrice.classList.add("namePrice")

        const title = document.createElement('div');
        title.classList.add("item-name");
        title.innerText = product.stock_name;

        const price = document.createElement('div');
        price.classList.add("price");
        price.innerText = "£" + parseFloat(product.stock_price).toFixed(2);

        const addToBasket = document.createElement('button');
        addToBasket.classList.add("add-to-basket");
        addToBasket.id = product.stock_id;
        addToBasket.innerText = "Add To Basket";

        // Add to basket click event for each stock_id
        addToBasket.addEventListener('click', () => {
            addToBasketHandler(product.stock_id);

            let inBasket = document.querySelector(`.inBasket[data-id="${product.stock_id}"]`);
            if (!inBasket) {    
                inBasket = document.createElement('div');
                inBasket.classList.add("inBasket");
                inBasket.dataset.id = product.stock_id;
                addToBasket.parentElement.appendChild(inBasket);
            }
            let qty = parseInt(inBasket.innerText.replace("Added: ", ""), 10) || 0;
            qty++;
            inBasket.innerText = "Added: " + qty;
            
        });

        namePrice.appendChild(title);
        namePrice.appendChild(price);

        productDetailsDiv.appendChild(img);
        productDetailsDiv.appendChild(namePrice);
        productDetailsDiv.appendChild(addToBasket);

        productDiv.appendChild(productDetailsDiv);

        ssProducts.appendChild(productDiv);
    }

    isFetching = false; // Reset fetching state for more scroll events
}

async function addToBasketHandler(stockId, quantity = 1) {
    try {
        const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/addToBasket.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ stock_id: stockId, quantity }),
        });

        if (response.status === 401) {
            // Redirect to login if unauthorized
            window.location.href = "https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php";
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log("Response Data:", data);
    } catch (error) {
        console.error("Error:", error);
    }
}


window.addEventListener('scroll', function () {
    if (isFetching) return;

    const scrollHeight = document.documentElement.scrollHeight;
    const scrollTop = document.documentElement.scrollTop;
    const clientHeight = document.documentElement.clientHeight;

    if (scrollTop + clientHeight >= scrollHeight - 50) {
        isFetching = true; // Prevent multiple fetches at once
        fetchProducts();
    }
});
