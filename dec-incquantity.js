// Function to increase the quantity
function increaseQuantity(input) {
    var quantityInput = input.previousElementSibling;
    var currentValue = parseInt(quantityInput.value);
    var maxValue = parseInt(quantityInput.max);
    if (currentValue < maxValue) {
        quantityInput.value = currentValue + 1;
    }
}

// Function to decrease the quantity
function decreaseQuantity(input) {
    var quantityInput = input.nextElementSibling;
    var currentValue = parseInt(quantityInput.value);
    if (currentValue > 1) {
        quantityInput.value = currentValue - 1;
    }
}
   