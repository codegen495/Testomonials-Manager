<?php
/*
Plugin Name: Testimonials Manager
Description: Easily manage and display customer testimonials on your WordPress site.
Version: 1.4
Author: Shohan Perera
*/

// Add a menu item in the WordPress admin dashboard
function testimonials_manager_menu() {
    add_menu_page('Testimonials Manager', 'Testimonials', 'manage_options', 'testimonials_manager', 'testimonials_manager_page');
}

add_action('admin_menu', 'testimonials_manager_menu');

// Testimonials Manager Page
function testimonials_manager_page() {
    if (isset($_POST['submit_testimonial'])) {
        // Handle form submission and save the testimonial
        $name = sanitize_text_field($_POST['testimonial_name']);
        $email = sanitize_email($_POST['testimonial_email']);
        $content = sanitize_textarea_field($_POST['testimonial_content']);
        $expiration_date = sanitize_text_field($_POST['testimonial_expiration_date']);

        // Save testimonial to the database
        save_testimonial_to_database($name, $email, $content, $expiration_date);

        // Send email notification to the admin
        send_email_notification($name, $email, $content);

        // Add moderation feature (set testimonial status to 'pending')
        set_testimonial_status_pending();

        // Add reply feature (set reply status to 'pending')
        set_testimonial_reply_pending();
    }

    // Display the testimonial submission form
    ?>
    <div class="wrap">
        <h1>Testimonials Manager</h1>
        <form method="post" action="">
            <label for="testimonial_name">Name:</label>
            <input type="text" name="testimonial_name" id="testimonial_name" required><br>

            <label for="testimonial_email">Email:</label>
            <input type="email" name="testimonial_email" id="testimonial_email" required><br>

            <label for="testimonial_content">Testimonial:</label>
            <textarea name="testimonial_content" id="testimonial_content" rows="4" required></textarea><br>

            <label for="testimonial_expiration_date">Expiration Date:</label>
            <input type="date" name="testimonial_expiration_date" id="testimonial_expiration_date"><br>

            <input type="submit" name="submit_testimonial" value="Submit Testimonial">
        </form>
    </div>
    <?php
}

// Function to save testimonial details to the database
function save_testimonial_to_database($name, $email, $content, $expiration_date) {
    $testimonials = get_testimonials_from_database();

    $new_testimonial = array(
        'name' => $name,
        'email' => $email,
        'content' => $content,
        'date' => date('Y-m-d H:i:s'),
        'status' => 'pending', // Default status set to 'pending' for moderation
        'expiration_date' => $expiration_date,
        'reply' => 'pending', // Default reply status set to 'pending'
        'rating' => 0, // Default rating set to 0
    );

    $testimonials[] = $new_testimonial;

    update_option('testimonials_list', $testimonials);
}

// Function to retrieve testimonial list from the database
function get_testimonials_from_database($status = 'published') {
    $testimonials = get_option('testimonials_list', array());

    // Filter testimonials based on status
    if ($status === 'published') {
        return array_filter($testimonials, function ($testimonial) {
            return $testimonial['status'] === 'published';
        });
    } elseif ($status === 'pending') {
        return array_filter($testimonials, function ($testimonial) {
            return $testimonial['status'] === 'pending';
        });
    }

    return $testimonials;
}

// Function to send email notification to the admin
function send_email_notification($name, $email, $content) {
    $admin_email = get_option('admin_email');

    $subject = 'New Testimonial Submission';
    $message = "Name: $name\nEmail: $email\nTestimonial: $content";

    wp_mail($admin_email, $subject, $message);
}

// Function to set testimonial status to 'pending'
function set_testimonial_status_pending() {
    $testimonials = get_testimonials_from_database('pending');

    $new_testimonial = array(
        'status' => 'pending',
    );

    $testimonials[] = $new_testimonial;

    update_option('testimonials_list', $testimonials);
}

// Function to set testimonial reply status to 'pending'
function set_testimonial_reply_pending() {
    $testimonials = get_testimonials_from_database();

    $new_testimonial = array(
        'reply' => 'pending',
    );

    $testimonials[] = $new_testimonial;

    update_option('testimonials_list', $testimonials);
}

// Shortcode to display testimonials
function display_testimonials_shortcode($atts) {
    // ... (unchanged)

    // Filter testimonials based on status
    $testimonials = get_testimonials_from_database($atts['status']);

    // ... (unchanged)

    return ob_get_clean();
}

// Additional Features

// Function to share testimonial on social media
function share_testimonial_social_media($testimonial_id) {
    $testimonial = get_testimonial_by_id($testimonial_id);

    if ($testimonial) {
        $testimonial_content = $testimonial['content'];
        $testimonial_url = get_permalink() . '?testimonial=' . $testimonial_id;

        // Example: Sharing on Twitter
        $twitter_share_url = "https://twitter.com/intent/tweet?text=$testimonial_content&url=$testimonial_url";
        echo '<a href="' . esc_url($twitter_share_url) . '" target="_blank">Share on Twitter</a>';

        // Example: Sharing on Facebook
        $facebook_share_url = "https://www.facebook.com/sharer/sharer.php?u=$testimonial_url";
        echo '<a href="' . esc_url($facebook_share_url) . '" target="_blank">Share on Facebook</a>';
    }
}

// Function to paginate displayed testimonials
function paginate_testimonials($page, $per_page) {
    $testimonials = get_testimonials_from_database('published');

    $total_testimonials = count($testimonials);
    $total_pages = ceil($total_testimonials / $per_page);

    // Ensure $page is within a valid range
    $page = max(1, min($total_pages, $page));

    // Calculate the offset for the query
    $offset = ($page - 1) * $per_page;

    // Get the testimonials for the current page
    $paged_testimonials = array_slice($testimonials, $offset, $per_page);

    // Display the testimonials
    foreach ($paged_testimonials as $testimonial) {
        // Display each testimonial
        echo '<div>';
        echo '<h3>' . esc_html($testimonial['name']) . '</h3>';
        echo '<p>' . esc_html($testimonial['content']) . '</p>';
        echo '</div>';
    }

    // Display pagination links
    echo '<div class="pagination">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $current_class = ($i == $page) ? 'current' : '';
        echo '<a class="' . esc_attr($current_class) . '" href="?page=' . esc_attr($i) . '">' . esc_html($i) . '</a>';
    }
    echo '</div>';
}

// ... (unchanged)

?>
