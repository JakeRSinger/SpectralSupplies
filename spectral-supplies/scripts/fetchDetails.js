window.addEventListener('load', fetchDetails);

async function fetchDetails() {
    try {
        // Fetch user details
        const response = await fetch("https://comp-server.uhi.ac.uk/~21011375/ss-webservice/details.php", {
        });
        
        if (!response.ok) {
            throw new Error(`Error fetching details: ${response.status}`);
        }

        const details = await response.json();

        if (details.Error) {
            console.error("Error in response:", details.Error);
        } else {
            showDetails(details);
            if (document.URL.endsWith("/account.html")) {
                autofill(details);
            }
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}

function showDetails(details) {
    const loginLink = document.getElementById("login-link");

    if (loginLink) {
        loginLink.href = "https://comp-server.uhi.ac.uk/~21011375/spectral-supplies/account.html";
        loginLink.textContent = `Account: (${details.user_forename})`;
    }
}

function autofill(details) {
    const forename = document.getElementById("new_user_forename");
    const surname = document.getElementById("new_user_surname");
    const email = document.getElementById("new_user_email");
    const addr1 = document.getElementById("new_user_addr_1");
    const addr2 = document.getElementById("new_user_addr_2");
    const town = document.getElementById("new_user_addr_town");
    const postcode = document.getElementById("new_user_postcode");
    const membership = document.getElementById("new_membership");

    // Check if the fields exist before assigning values
    if (forename) forename.value = details.user_forename;
    if (surname) surname.value = details.user_surname;
    if (email) email.value = details.user_email;
    if (addr1) addr1.value = details.user_addr_1;
    if (addr2) addr2.value = details.user_addr_2;
    if (town) town.value = details.user_addr_town;
    if (postcode) postcode.value = details.user_postcode;

    // Convert user_discount to float for proper comparison
    const discount = parseFloat(details.user_discount);

    // Set membership level based on user discount
    if (membership) {
        switch (discount) {
            case 0.00:
                membership.value = "bronze";
                break;
            case 0.10:
                membership.value = "silver";
                break;
            case 0.20:
                membership.value = "gold";
                break;
            default:
                membership.value = "bronze"; // Default to bronze if the discount doesn't match any known level
                break;
        }
    }
}
