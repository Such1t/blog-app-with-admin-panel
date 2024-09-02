<?php
include 'partials/header.php';
?>
<style>
    .contact-page {
        padding: 2rem 0;
        background-color: #f9f9f9;
    }

    .contact-page__container {
        max-width: 800px;
        margin: 0 auto;
        padding: 1rem;
        background-color: #ffffff;
        border-radius: var(--card-border-radius-1);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .contact-page h2 {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #6f6af8;
    }

    .contact-page p {
        font-size: 1rem;
        margin-bottom: 1.5rem;
        color: #6f6af8;
    }

    .contact-form {
        display: flex;
        flex-direction: column;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #6f6af8;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: var(--card-border-radius-2);
        font-size: 1rem;
        color: #6f6af8;
    }

    .form-group textarea {
        resize: vertical;
    }

    .btn-submit {
        background-color: #6f6af8;
        color: #ffffff;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: var(--card-border-radius-2);
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .btn-submit:hover {
        background-color: #5a5aeb;
    }

    .contact-info {
        margin-top: 2rem;
        color: blue; /* Added this line to change text color to blue */
    }

    .contact-info h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: blue; /* Added this line to change text color to blue */
    }

    .contact-info ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
    }

    .contact-info li {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        color: blue; /* Added this line to change text color to blue */
    }

    .contact-info a {
        color: blue; /* Added this line to change link color to blue */
        text-decoration: none;
    }

    .contact-info a:hover {
        text-decoration: underline;
    }

    .contact-info strong {
        color: blue; /* Added this line to change strong element color to blue */
    }

    .contact-info p {
        color: blue; /* Added this line to change paragraph text color to blue */
    }
</style>
<section class="contact-page">
    <div class="container contact-page__container">
        <h2>Contact Us</h2>
        <p>We'd love to hear from you! Whether you have a question about our services or just want to say hi, feel free to reach out to us using the form below.</p>

        <div class="contact-info">
            <h3>Contact Information</h3>
            <p>For any urgent inquiries, you can reach us at:</p>
            <ul>
                <li><strong>Address:</strong> Sanquelim-Goa,India</li>
                <li><strong>Email:</strong> <a href="mailto:info@example.com">suchitmashelkar11@gmail.com</a></li>
                <li><strong>Phone:</strong> <a href="tel:+919665648532">+919665648532</a></li>
            </ul>
        </div>
    </div>
</section>

<?php
include 'partials/footer.php';
?>
