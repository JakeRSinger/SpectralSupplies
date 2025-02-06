window.addEventListener('load', fetchBasket);

async function fetchBasket() {
    try {
        const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/basket.php");
        
        if (!response.ok) {
            throw new Error(`Error fetching basket: ${response.status}`);
        }

        const data = await response.json();
        console.log("Basket data:", data); // Debug log

        if (data.basket_items && Array.isArray(data.basket_items)) {
            writeBasket(data.basket_items); // Pass the array to writeBasket
        } else if (data.Error) {
            console.error("Error in response:", data.Error);
        } else {
            console.error("Unexpected response format:", data);
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}

function writeBasket(basket) {
    const sectionSecondary = document.getElementById('section-secondary');
    const ssProducts = document.getElementById('basket');
    ssProducts.innerHTML = ''; // Clear previous content

    if (basket.length === 0) {
        ssProducts.innerText = "Your basket is empty.";
        document.getElementById('checkout')?.remove(); // Remove checkout button if it exists
        return;
    }
    
    // Ensure a single checkout button exists
    let checkout = document.getElementById('checkout');
    if (!checkout) {
        checkout = document.createElement('button');
        checkout.classList.add('checkout');
        checkout.id = "checkout";
        checkout.innerText = "Checkout";
        checkout.addEventListener('click', checkoutHandler);
        sectionSecondary.appendChild(checkout);
    }

    for (const item of basket) {
        if (!item.stock_id || !item.stock_name || !item.bi_price_per_unit || !item.bi_qty) {
            console.error("Incomplete basket item data:", item);
            continue; // Skip this item
        }

        const productDiv = document.createElement('div');
        productDiv.classList.add("product");

        const img = document.createElement('img');
        img.src = `https://comp-server.uhi.ac.uk/~21011375/ss-webservice/img/${item.stock_id}.png`;
        img.alt = item.stock_name;

        const productDetailsDiv = document.createElement('div');
        productDetailsDiv.classList.add("item-details");

        const namePrice = document.createElement('div');
        namePrice.classList.add("namePrice");

        const title = document.createElement('div');
        title.classList.add("item-name");
        title.innerText = item.stock_name;

        const price = document.createElement('div');
        price.classList.add("price");
        price.innerText = "£" + parseFloat(item.bi_price_per_unit).toFixed(2);

        const quantity = document.createElement('div');
        quantity.classList.add("quantity");
        quantity.innerText = `Quantity: ${item.bi_qty}`;

        const removeItem = document.createElement('button');
        removeItem.classList.add("remove-item");
        removeItem.id = item.stock_id;
        removeItem.innerText = "Remove Item";

        removeItem.addEventListener('click', () => {
            removeItemHandler(item.stock_id);
        });

        namePrice.appendChild(title);
        namePrice.appendChild(price);

        productDetailsDiv.appendChild(img);
        productDetailsDiv.appendChild(namePrice);
        productDetailsDiv.appendChild(quantity);
        productDetailsDiv.appendChild(removeItem);

        productDiv.appendChild(productDetailsDiv);

        ssProducts.appendChild(productDiv);
    }
}

async function removeItemHandler(stockId, quantity = -1) {
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

        fetchBasket();
    } catch (error) {
        console.error("Error:", error);
    }
}

async function checkoutHandler() {
    try {
        // Fetch checkout endpoint
        const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/checkout.php");

        if (response.status === 401) {
            // Redirect to login if unauthorized
            window.location.href = "https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/login.php";
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Parse the response to confirm successful checkout
        const data = await response.json();
        console.log("Checkout response data:", data);

        // Redirect to invoice page
        invoice();

    } catch (error) {
        console.error("Error during checkout:", error);
    }
}

function invoice() {
    // Redirect to invoice page
    window.location.reload();
    window.location.href = "https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/invoice.html"; 
}