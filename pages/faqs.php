<?php
$pageTitle = "Frequently Asked Questions";
include '../includes/header.php';
?>

<main class="faqs-page">
    <div class="container">
        <div class="page-header">
            <h1>Frequently Asked Questions</h1>
            <nav class="breadcrumb">
                <a href="/isabelle-prints/">Home</a> / FAQs
            </nav>
        </div>

        <div class="faqs-content">
            <div class="faq-item">
                <div class="faq-question">
                    <h3>What printing services do you offer?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>We offer a wide range of printing services including business cards, banners, flyers, brochures, posters, and custom designs. All our products are printed using high-quality materials and state-of-the-art printing technology.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>What file formats do you accept?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>We accept various file formats including PDF, AI, PSD, JPG, PNG, and EPS. For best results, we recommend submitting your files in PDF format with high resolution (300 DPI minimum).</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How long does it take to complete an order?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Standard orders typically take 3-5 business days to complete. Rush orders can be completed in 1-2 business days for an additional fee. Custom designs may require additional time depending on complexity.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do you offer design services?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes! Our team of experienced designers can create custom designs for your printing needs. We offer logo design, business card design, banner design, and complete branding packages.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>What are your payment options?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>We accept various payment methods including cash, credit cards (Visa, MasterCard, American Express), bank transfers, and online payments through our secure payment gateway.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do you offer delivery services?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, we offer delivery services within the local area. Delivery fees vary based on location and order size. We also offer pickup services at our location for your convenience.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Can I see a proof before printing?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>We provide digital proofs for all orders before printing. You can review and approve the proof, or request changes if needed. We don't proceed with printing until you're completely satisfied.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>What if I'm not satisfied with my order?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Customer satisfaction is our priority. If you're not completely satisfied with your order, please contact us within 48 hours of delivery. We'll work with you to resolve any issues and ensure you're happy with the final product.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>Do you offer bulk discounts?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Yes, we offer competitive bulk pricing for large orders. The more you order, the more you save! Contact us with your requirements for a custom quote on bulk orders.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    <h3>How can I track my order?</h3>
                    <span class="faq-toggle">+</span>
                </div>
                <div class="faq-answer">
                    <p>Once your order is placed, you'll receive an order confirmation with a tracking number. You can use this number to check the status of your order on our website or contact our customer service team for updates.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const toggle = item.querySelector('.faq-toggle');
        
        question.addEventListener('click', function() {
            const isOpen = item.classList.contains('active');
            
            // Close all other items
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
                otherItem.querySelector('.faq-toggle').textContent = '+';
            });
            
            // Toggle current item
            if (!isOpen) {
                item.classList.add('active');
                toggle.textContent = '-';
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>