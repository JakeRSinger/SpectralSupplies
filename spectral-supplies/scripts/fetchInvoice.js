window.addEventListener('load', () => {
    fetchInvoice();
    fetchDetails();
});

async function fetchInvoice() {
    try {
        const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/orderDetails.php");
        
        if (!response.ok) {
            throw new Error(`Error fetching order: ${response.status}`);
        }

        const data = await response.json();
        console.log("Order data:", data); // Debug log

        if (data.order_items) {
            writeOrder(data.order_items); // Pass the array to writeOrder
        } else if (data.Error) {
            console.error("Error in response:", data.Error);
        } else {
            console.error("Unexpected response format:", data);
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}


async function fetchDetails() {
    try {
        // Fetch user details
        const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/details.php", {
        });
        
        if (!response.ok) {
            throw new Error(`Error fetching details: ${response.status}`);
        }

        const details = await response.json();
        console.log("User details:", details); // Debug log

        if (details.Error) {
            console.error("Error in response:", details.Error);
        } else {
            writeDetails(details);
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}

function writeOrder(order_items) {
    const items = document.getElementById("items");
    let total_price = 0; // Corrected variable name

    for (const item of order_items) {
        const productDiv = document.createElement('div');
        productDiv.classList.add("product");

        const img = document.createElement('img');
        img.src = "https://comp-server.uhi.ac.uk/~21011375/ss-webservice/img/" + item.ol_stock_id + ".png";
        img.alt = item.stock_name;

        const productDetailsDiv = document.createElement('div');
        productDetailsDiv.classList.add("item-details");

        const namePrice = document.createElement('div');
        namePrice.classList.add("namePrice");

        const title = document.createElement('div');
        title.classList.add("item-name");
        title.innerText = item.stock_name;

        const qty = document.createElement('div');
        qty.classList.add("qty");
        qty.innerText = "Qty: " + item.ol_qty;
        
        const price = document.createElement('div');
        price.classList.add("price");
        price.innerText = "Total Price: £" + parseFloat(item.ol_total).toFixed(2);
        total_price += parseFloat(item.ol_total); // Corrected to accumulate the total price

        namePrice.appendChild(title);
        namePrice.appendChild(price);
        namePrice.appendChild(qty);

        productDetailsDiv.appendChild(img);
        productDetailsDiv.appendChild(namePrice);

        productDiv.appendChild(productDetailsDiv);

        items.appendChild(productDiv); // Corrected to append to items
    }

    // Pricing Details
    const pricing = document.getElementById("pricing");

    const totalPriceDiv = document.createElement('div');
    totalPriceDiv.classList.add("totalPrice");
    totalPriceDiv.innerText = "Total Price: \n £" + total_price.toFixed(2);

    const excVAT = document.createElement('div');
    excVAT.classList.add("excVAT");
    excVAT.innerText = "Excluding VAT (17.5%): \n £" + (total_price / 1.175).toFixed(2); // Fixed VAT calculation

    const memberDiscount = document.createElement('div');
    memberDiscount.classList.add("memberDiscount");
    memberDiscount.innerText = "Discount: \n £" + (total_price - parseFloat(order_items[0].order_total)).toFixed(2); // Convert to number before calculating discount

    const finalPrice = document.createElement('div');
    finalPrice.classList.add("finalPrice");
    finalPrice.innerText = "Total (with discount): \n £" + parseFloat(order_items[0].order_total).toFixed(2); // Convert to number before displaying final price

    const contBtn = document.createElement('button');
    contBtn.classList.add("contBtn");
    contBtn.innerText = "Continue";
    contBtn.addEventListener('click', function() {
        window.location.href = "https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/index.html";
    });

    pricing.appendChild(totalPriceDiv);
    pricing.appendChild(excVAT);
    pricing.appendChild(memberDiscount);
    pricing.appendChild(finalPrice);
    pricing.appendChild(contBtn);
}


function writeDetails(details) {
    const userDetails = document.getElementById('user-details');

    const forename = document.createElement('div');
    forename.classList.add("forename");
    forename.innerText = "Forename: \n" + details.user_forename;

    const surname = document.createElement('div');
    surname.classList.add("surname");
    surname.innerText = "Surname: \n"+ details.user_surname;

    const email = document.createElement('div');
    email.classList.add("email");
    email.innerText = "Email: " + details.user_email;

    const address = document.createElement('div');
    address.classList.add("address");
    address.innerText = "Address:"

    const addr1 = document.createElement('div');
    addr1.classList.add("addr1");
    addr1.innerText = details.user_addr_1;

    const addr2 = document.createElement('div');
    addr2.classList.add("addr2");
    addr2.innerText = details.user_addr_2;

    const town = document.createElement('div');
    town.classList.add("town");
    town.innerText = details.user_addr_town;

    const postcode = document.createElement('div');
    postcode.classList.add("postcode");
    postcode.innerText = details.user_postcode;

    const membership = document.createElement('div');
    membership.classList.add("membership");
    const discount = parseFloat(details.user_discount);
    switch (discount) {
        case 0.00:
            membership.innerText = "Membership: \n Bronze";
            break;
        case 0.10:
            membership.innerText = "Membership: \n Silver";
            break;
        case 0.20:
            membership.innerText = "Membership: \n Gold";
            break;
        default:
            membership.innerText = "Membership: \n Bronze";
            break;
    }

    address.appendChild(addr1);
    address.appendChild(addr2);
    address.appendChild(town);
    address.appendChild(postcode);

    userDetails.appendChild(forename);
    userDetails.appendChild(surname);
    userDetails.appendChild(email);    
    userDetails.appendChild(address)
    userDetails.appendChild(membership);
}