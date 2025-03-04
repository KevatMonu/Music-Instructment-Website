<?php
  $pageTitle = "About Us | K&P Music";
  include('pages/header.php');
?>

<main class="about-us-container">
  <section class="hero-section">
    <h1>About K&P Music</h1>
    <p class="tagline">Passionate About Music Since 2021</p>
  </section>

  <section class="our-story">
    <div class="about-container">
      <h2>Our Story</h2>
      <div class="story-content">
        <div class="story-image">
          <img src="assets/home/image/logo.png" alt="K&P Music Founders" />
        </div>
        <div class="story-text">
          <p>K&P Music was founded in 2021 by Monu Kevat and Harshal Pawar, two passionate musicians who dreamed of creating a space where musicians of all levels could find quality instruments and expert advice.</p>
          <p>What started as a small  shop and expanded to the online world, bringing the joy of music to a wider audience, has grown from a humble collection of guitars and keyboards into <?php echo date('Y') - 2021; ?> years of serving our community with the finest selection of instruments, accessories, and music education resources. Through passion, dedication, and a commitment to quality, we continue to inspire musicians of all levels, whether they’re beginners picking up their first instrument or professionals perfecting their craft.</p>
        </div>
      </div>
    </div>
  </section>

  <section class="our-values">
    <h2>Our Values</h2>
    <div class="values-grid">
      <div class="value-card">
        <i class="fa fa-star"></i>
        <h3>Quality</h3>
        <p>We personally select every instrument in our inventory to ensure it meets our high standards of craftsmanship and sound quality.</p>
      </div>
      <div class="value-card">
        <i class="fa fa-users"></i>
        <h3>Community</h3>
        <p>We believe in building a vibrant music community through events, workshops, and supporting local musicians and schools.</p>
      </div>
      <div class="value-card">
        <i class="fa fa-graduation-cap"></i>
        <h3>Education</h3>
        <p>Our in-store lessons and workshops help musicians of all ages develop their skills and deepen their love of music.</p>
      </div>
      <div class="value-card">
        <i class="fa fa-globe"></i>
        <h3>Innovation</h3>
        <p>We constantly explore new technologies, instrument designs, and teaching methods to keep music education and performance cutting-edge.</p>
      </div>
    </div>
  </section>

  <section class="our-team">
    <h2>Meet Our Team</h2>
    <div class="team-members">
      <?php
        // You would typically pull this from a database, but using an array for demonstration
        $team = array(
          array(
            'name' => 'Arjun Bodhan',
            'position' => 'Store Manager',
            'bio' => 'Son of our founders, David has been surrounded by music his entire life. With expertise in brass and woodwind instruments, he leads our team with passion and dedication.',
            'image' => 'david-reynolds.jpg'
          ),
          array(
            'name' => 'Gaurav Wadkar',
            'position' => 'String Specialist',
            'bio' => 'A classically trained violinist with over 15 years of performance experience, Sarah helps customers find their perfect string instrument, from beginner violins to professional cellos.',
            'image' => 'sarah-chen.jpg'
          ),
          array(
            'name' => 'Sahil Kondalkar',
            'position' => 'Guitar Expert',
            'bio' => 'With deep knowledge of electric and acoustic guitars, Marcus has been helping guitarists find their sound at K&P Music for over a decade.',
            'image' => 'marcus-johnson.jpg'
          ),
          array(
            'name' => 'Parvin Pawar',
            'position' => 'Music Education Director',
            'bio' => 'Lisa coordinates our lesson programs and workshops, bringing her experience as a music educator to help students of all ages discover the joy of making music.',
            'image' => 'lisa-patel.jpg'
          )
        );

        foreach($team as $member) {
          echo '<div class="team-member">';
          echo '<img src="images/team/' . $member['image'] . '" alt="' . $member['name'] . '" />';
          echo '<h3>' . $member['name'] . '</h3>';
          echo '<p class="position">' . $member['position'] . '</p>';
          echo '<p class="bio">' . $member['bio'] . '</p>';
          echo '</div>';
        }
      ?>
    </div>
  </section>

  <section class="store-info">
    <h2>Visit Our Store</h2>
    <div class="store-details">
      <div class="map">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3107.5344928479843!2d-77.37768538256397!3d38.81548505552726!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89b64e96394b91ef%3A0xe2fb177a68ff8ba!2sMusic%20%26%20Arts!5e0!3m2!1sen!2sus!4v1709605054301!5m2!1sen!2sus" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <p class="map-link"><a href="https://maps.app.goo.gl/h6mJA4EwfNfCkZ7g8" target="_blank">View on Google Maps</a></p>
      </div>
      <div class="contact-info">
        <p><strong>Address:</strong> 123 Melody Lane, Harmony City, HC 12345</p>
        <p><strong>Phone:</strong> (555) 123-4567</p>
        <p><strong>Hours:</strong></p>
        <ul>
          <li>Monday - Friday: 10am - 8pm</li>
          <li>Saturday: 9am - 6pm</li>
          <li>Sunday: 11am - 5pm</li>
        </ul>
        <a href="contact.php" class="btn">Contact Us</a>
      </div>
    </div>
  </section>

  <section class="testimonials">
    <h2>What Our Customers Say</h2>
    <div class="testimonial-slider">
      <?php
        // Again, this would typically come from a database
        $testimonials = array(
          array(
            'quote' => 'K&P Music has been my go-to music store for over 10 years. Their expertise and friendly service keep me coming back.',
            'author' => 'Michael T., Guitarist'
          ),
          array(
            'quote' => 'The staff at K&P helped my daughter find her first violin and have supported her musical journey ever since. We couldn\'t ask for better guidance!',
            'author' => 'Jennifer L., Parent'
          ),
          array(
            'quote' => 'As a professional musician, I appreciate the quality instruments and knowledgeable staff at K&P. They understand the needs of performers at every level.',
            'author' => 'Robert W., Professional Pianist'
          )
        );

        foreach($testimonials as $testimonial) {
          echo '<div class="testimonial">';
          echo '<blockquote>"' . $testimonial['quote'] . '"</blockquote>';
          echo '<p class="author">— ' . $testimonial['author'] . '</p>';
          echo '</div>';
        }
      ?>
    </div>
  </section>
</main>

<?php include('pages/footer.php'); // Assuming you have a footer file ?>