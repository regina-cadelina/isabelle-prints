// Main JavaScript functionality

// Product Modal Functions
function openProductModal(productId) {
    const modal = document.getElementById("productModal")
    const modalContent = document.getElementById("modalContent")
  
    // Show loading
    modalContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>'
    modal.style.display = "block"
  
    // Fetch product details
    fetch(`/isabelle-prints/api/product-details.php?id=${productId}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.text()
      })
      .then((html) => {
        modalContent.innerHTML = html
  
        // Initialize any form elements or event listeners in the modal
        initializeModalElements()
      })
      .catch((error) => {
        console.error("Error loading product details:", error)
        modalContent.innerHTML = `
                  <div class="error-message">
                      <i class="fas fa-exclamation-circle"></i>
                      <p>Sorry, we couldn't load the product details. Please try again later.</p>
                      <button class="btn btn-primary" onclick="closeModal()">Close</button>
                  </div>
              `
      })
  }
  
  function initializeModalElements() {
    // Initialize color options
    const colorOptions = document.querySelectorAll(".color-option")
    if (colorOptions.length > 0) {
      colorOptions.forEach((option) => {
        option.addEventListener("click", function () {
          // Remove active class from all options
          colorOptions.forEach((opt) => opt.classList.remove("active"))
          // Add active class to clicked option
          this.classList.add("active")
          // Update hidden input value
          const colorInput = document.querySelector('input[name="color"]')
          if (colorInput) {
            colorInput.value = this.dataset.color
          }
          // Update price if needed
          updatePrice()
        })
      })
    }
  
    // Initialize select options for price updates
    const selectOptions = document.querySelectorAll(".product-options select")
    selectOptions.forEach((select) => {
      select.addEventListener("change", updatePrice)
    })
  
    // Set initial price
    updatePrice()
  }
  
  function updatePrice() {
    const priceElement = document.querySelector(".price")
    if (!priceElement) return
  
    const basePrice = Number.parseFloat(priceElement.textContent.replace("₱", "").replace(",", "")) || 0
    let totalPrice = basePrice
  
    // Add price modifiers from selected options
    const selects = document.querySelectorAll(".product-options select")
    selects.forEach((select) => {
      const selectedOption = select.options[select.selectedIndex]
      if (selectedOption && selectedOption.dataset.price) {
        totalPrice += Number.parseFloat(selectedOption.dataset.price)
      }
    })
  
    // Add price modifier from color option
    const activeColorOption = document.querySelector(".color-option.active")
    if (activeColorOption && activeColorOption.dataset.price) {
      totalPrice += Number.parseFloat(activeColorOption.dataset.price)
    }
  
    // Update displayed price
    priceElement.textContent =
      "₱" + totalPrice.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  }
  
  function closeModal() {
    const modal = document.getElementById("productModal")
    modal.style.display = "none"
  }
  
  function addToCart(event, productId) {
    event.preventDefault()
  
    const form = event.target
    const formData = new FormData(form)
    formData.append("product_id", productId)
    formData.append("action", "add_to_cart")
  
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]')
    const originalText = submitBtn.textContent
    submitBtn.textContent = "Adding to Cart..."
    submitBtn.disabled = true
  
    fetch("/isabelle-prints/api/cart.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Update cart count in header
          updateCartCount()
  
          // Show success message
          submitBtn.textContent = "Added to Cart!"
          submitBtn.style.backgroundColor = "#28a745"
  
          // Close modal after 1 second
          setTimeout(() => {
            closeModal()
            submitBtn.textContent = originalText
            submitBtn.disabled = false
            submitBtn.style.backgroundColor = ""
          }, 1000)
        } else {
          alert(data.message || "Error adding item to cart")
          submitBtn.textContent = originalText
          submitBtn.disabled = false
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("Error adding item to cart")
        submitBtn.textContent = originalText
        submitBtn.disabled = false
      })
  }
  
  // Close modal when clicking the X or outside
  document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("productModal")
    const closeBtn = document.querySelector(".close")
  
    if (closeBtn) {
      closeBtn.onclick = () => {
        closeModal()
      }
    }
  
    window.onclick = (event) => {
      if (event.target == modal) {
        closeModal()
      }
    }
  })
  
  // Cart quantity functions - FIXED VERSION
  function changeQuantity(change) {
    const input = document.querySelector(".qty-input")
    if (input) {
      const currentValue = Number.parseInt(input.value) || 1
      const maxValue = Number.parseInt(input.getAttribute("max")) || 1000
      const minValue = Number.parseInt(input.getAttribute("min")) || 1
      const newValue = Math.max(minValue, Math.min(maxValue, currentValue + change))
      input.value = newValue
    }
  }
  
  // Update cart count in header
  function updateCartCount() {
    fetch("/isabelle-prints/api/cart-count.php")
      .then((response) => response.json())
      .then((data) => {
        const cartCountElement = document.querySelector(".cart-count")
        if (data.count > 0) {
          if (cartCountElement) {
            cartCountElement.textContent = data.count
          } else {
            // Create cart count element if it doesn't exist
            const cartIcon = document.querySelector(".cart-icon")
            if (cartIcon) {
              const countSpan = document.createElement("span")
              countSpan.className = "cart-count"
              countSpan.textContent = data.count
              cartIcon.appendChild(countSpan)
            }
          }
        } else {
          if (cartCountElement) {
            cartCountElement.remove()
          }
        }
      })
      .catch((error) => console.error("Error updating cart count:", error))
  }
  
  function filterByCategory(categoryId) {
    const currentUrl = new URL(window.location.href)
    if (categoryId) {
      currentUrl.searchParams.set("category", categoryId)
    } else {
      currentUrl.searchParams.delete("category")
    }
    window.location.href = currentUrl.toString()
  }
  
  function sortProducts(sortValue) {
    const currentUrl = new URL(window.location.href)
    currentUrl.searchParams.set("sort", sortValue)
    window.location.href = currentUrl.toString()
  }
  
  // Show notification
  function showNotification(message, type = "info") {
    const notification = document.createElement("div")
    notification.className = `notification notification-${type}`
    notification.textContent = message
  
    // Add styles
    notification.style.cssText = `
          position: fixed;
          top: 20px;
          right: 20px;
          padding: 1rem 1.5rem;
          border-radius: 4px;
          color: white;
          font-weight: 500;
          z-index: 9999;
          animation: slideIn 0.3s ease;
      `
  
    if (type === "success") {
      notification.style.backgroundColor = "#28a745"
    } else if (type === "error") {
      notification.style.backgroundColor = "#dc3545"
    } else {
      notification.style.backgroundColor = "#007bff"
    }
  
    document.body.appendChild(notification)
  
    // Remove after 3 seconds
    setTimeout(() => {
      notification.style.animation = "slideOut 0.3s ease"
      setTimeout(() => {
        notification.remove()
      }, 300)
    }, 3000)
  }
  
  // Add CSS animations and styles
  const style = document.createElement("style")
  style.textContent = `
      @keyframes slideIn {
          from { transform: translateX(100%); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
      }
      
      @keyframes slideOut {
          from { transform: translateX(0); opacity: 1; }
          to { transform: translateX(100%); opacity: 0; }
      }
      
      .color-options {
          display: flex;
          gap: 10px;
          flex-wrap: wrap;
      }
      
      .color-option {
          width: 30px;
          height: 30px;
          border-radius: 50%;
          border: 2px solid #ddd;
          cursor: pointer;
          transition: all 0.3s ease;
      }
      
      .color-option.active {
          border-color: #007bff;
          transform: scale(1.1);
          box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
      }
      
      .color-option:hover {
          transform: scale(1.05);
      }
      
      .quantity-controls {
          margin: 15px 0;
      }
      
      .qty-wrapper {
          display: flex;
          align-items: center;
          gap: 0;
          width: fit-content;
          border: 1px solid #ddd;
          border-radius: 4px;
          overflow: hidden;
      }
      
      .qty-btn {
          background: #f8f9fa;
          border: none;
          padding: 8px 12px;
          cursor: pointer;
          font-size: 16px;
          font-weight: bold;
          transition: background-color 0.2s;
          min-width: 40px;
      }
      
      .qty-btn:hover {
          background: #e9ecef;
      }
      
      .qty-btn:active {
          background: #dee2e6;
      }
      
      .qty-input {
          border: none;
          text-align: center;
          width: 60px;
          padding: 8px 4px;
          font-size: 16px;
          background: white;
      }
      
      .qty-input:focus {
          outline: none;
      }
      
      .product-modal-image {
          max-width: 300px;
          margin-right: 20px;
      }
      
      .product-modal-image img {
          width: 100%;
          height: auto;
          border-radius: 8px;
      }
      
      .placeholder-image {
          width: 100%;
          height: 200px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f8f9fa;
          border-radius: 8px;
          color: #6c757d;
          font-size: 48px;
      }
  `
  document.head.appendChild(style)
  
  // Initialize page
  document.addEventListener("DOMContentLoaded", () => {
    // Update cart count on page load
    updateCartCount()
  
    // Add smooth scrolling to anchor links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", function (e) {
        e.preventDefault()
        const target = document.querySelector(this.getAttribute("href"))
        if (target) {
          target.scrollIntoView({
            behavior: "smooth",
          })
        }
      })
    })
  })
  