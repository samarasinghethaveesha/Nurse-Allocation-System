<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section text-white text-center py-5" style="background: url('assets/images/hero-bg.jpg') no-repeat center center; background-size: cover;">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to NurseCare</h1>
        <p class="lead">Efficient Nurse Allocation for a Healthier Tomorrow</p>
        <a href="#services" class="btn btn-primary btn-lg mt-3">Explore Services</a>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h2>About NurseCare</h2>
                <p>NurseCare is an innovative platform designed to allocate nurses efficiently, ensuring fair pricing and optimal availability. Nurses are assigned based on skills and demand, providing on-demand staffing solutions for both individuals and corporations.</p>
            </div>
            <div class="col-md-6">
                <img src="assets/images/about-img.jpg" alt="Nurse Care Team" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-5">Our Features</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-body">
                        <h5 class="card-title">Nurse Registration</h5>
                        <p class="card-text">Register qualified nurses with their working hours and skill sets.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-body">
                        <h5 class="card-title">Customer Management</h5>
                        <p class="card-text">Seamless user registration, profile management, and appointment scheduling.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow">
                    <div class="card-body">
                        <h5 class="card-title">Shift Scheduling</h5>
                        <p class="card-text">Automated shift allocation based on nurse availability and workload.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonial Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">What Our Users Say</h2>
        <div class="row">
            <div class="col-md-4">
                <blockquote class="blockquote">
                    <p>"Revolutionized how we manage nursing staff. Highly efficient!"</p>
                    <footer class="blockquote-footer">John D., Hospital Admin</footer>
                </blockquote>
            </div>
            <div class="col-md-4">
                <blockquote class="blockquote">
                    <p>"Easy booking and excellent nurse availability during emergencies."</p>
                    <footer class="blockquote-footer">Sarah M., Private Client</footer>
                </blockquote>
            </div>
            <div class="col-md-4">
                <blockquote class="blockquote">
                    <p>"Transparent payment system makes everything hassle-free."</p>
                    <footer class="blockquote-footer">Lakshan T., Nurse</footer>
                </blockquote>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section text-white text-center py-5" style="background-color: #0d6efd;">
    <div class="container">
        <h2>Ready to Get Started?</h2>
        <p class="lead">Join NurseCare today and experience seamless nurse allocation.</p>
        <a href="#contact" class="btn btn-light btn-lg">Contact Us</a>
    </div>
</section>

<!-- Contact Form -->
<section id="contact" class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Contact Us</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <form action="send_email.php" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>