<?php
include 'partials/header.php';
?>
<style>
    .about__page {
        padding: 2rem;
        background-color: #f9f9f9;
    }

    .about__container {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
    }

    .about__container h2 {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        color: #6f6af8;
    }

    .about__container p {
        font-size: 1.1rem;
        line-height: 1.6;
        color: #6f6af8;
        margin-bottom: 1.5rem;
    }

    .about__container ul {
        text-align: left;
        margin: 1.5rem auto;
        max-width: 600px;
    }

    .about__container li {
        margin-bottom: 1rem;
        font-size: 1.1rem;
        color: #6f6af8;
    }

    .about__container a {
        color: #6f6af8;
        text-decoration: none;
    }

    .about__container a:hover {
        text-decoration: underline;
    }
 h3{
     color:#6f6af8;
 }
 about__container ul li {
     color:#6f6af8;
 }
</style>

<section class="about__page">
    <div class="container about__container">
        <h2>About Us</h2>
        <p>
            Welcome to Dialogue, your go-to platform for sharing thoughts, ideas, and stories. Our mission is to provide a space where voices from all walks of life can be heard. Whether you're here to blog, share creative content, or engage in meaningful discussions, Dialogue is the place for you.
        </p>
        <h3>Our Story</h3>
        <p>
            Dialogue was founded with the belief that everyone has a story worth sharing. Our platform was designed to be intuitive, user-friendly, and inclusive, allowing people from around the world to connect, share, and inspire.
        </p>
        <h3>Our Values</h3>
        <ul>
            <li><strong>Inclusivity:</strong> We believe in giving everyone a voice, regardless of their background or beliefs.</li>
            <li><strong>Creativity:</strong> We celebrate creativity in all its forms and encourage our users to express themselves freely.</li>
            <li><strong>Community:</strong> Dialogue is more than just a platform; it's a community of like-minded individuals who support and uplift one another.</li>
        </ul>
        <h3>Meet the Team</h3>
        <p>
            Our team is made up of passionate individuals dedicated to creating a positive and engaging environment for our users. From our developers to our community managers, everyone at Dialogue is committed to making your experience the best it can be.</p>
        <h3>Contact Us</h3>
        <p>
            Have questions or feedback? We'd love to hear from you! Visit our <a href="contact.php">Contact</a> page to get in touch with us.
        </p>
    </div>
</section>

<?php
include 'partials/footer.php';
?>
