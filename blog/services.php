<?php
include 'partials/header.php';
?>
<style>
    /* Container for the entire services section */
    .services__page {
        padding: 4rem 0;
        background-color: #f9f9f9;
        text-align: center;
    }

    /* General container styling */
    .services__page .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    /* Main heading styling */
    .services__page h2 {
        font-size: 2.5rem;
        color: #6f6af8;
        margin-bottom: 2rem;
        font-weight: 700;
    }

    /* List of services */
    .services__list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    /* Individual service item */
    .service__item {
        background: #ffffff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    /* Service item hover effect */
    .service__item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    /* Service item heading */
    .service__item h3 {
        font-size: 1.75rem;
        color: #6f6af8;
        margin-bottom: 1rem;
    }

    /* Service item description */
    .service__item p {
        font-size: 1.1rem;
        color: #666666;
        line-height: 1.6;
    }

    /* Media queries for responsiveness */
    @media (max-width: 768px) {
        .services__list {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

</style>

<section class="services__page">
    <div class="container">
        <h2>Our Services</h2>
        <p>At Dialogue Blog, we offer a comprehensive suite of services designed to empower bloggers, independent writers, and content companies. Our platform is built to help you create, manage, and optimize your content with ease.</p>

        <div class="services__list">
            <div class="service__item">
                <h3>Content Creation</h3>
                <p>Our platform features a powerful content management system (CMS) that includes a rich text editor (Quill) to help you write and customize your blog posts. You can easily add images, format your text, and create engaging content that resonates with your audience.</p>
            </div>

            <div class="service__item">
                <h3>User Engagement Tools</h3>
                <p>Engage with your readers through our integrated commenting system, like feature, and follower system. Encourage interactions and build a community around your content.</p>
            </div>

            <div class="service__item">
                <h3>Analytics & Performance Tracking</h3>
                <p>Monitor the performance of your posts.Track metrics such as views, likes, comments, and follower growth to understand what content works best with your audience.</p>
            </div>

        </div>
    </div>
</section>

<?php
include 'partials/footer.php';
?>
