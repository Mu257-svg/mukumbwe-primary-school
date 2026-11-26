<?php
// Load Google Fonts & Font Awesome
function mps_enqueue_assets() {
    // FIX: Changed handle to 'mps-custom-icons' to prevent Hello Elementor conflicts
    wp_enqueue_style('mps-custom-icons', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Great+Vibes&display=swap', array(), null);
}
add_action('wp_enqueue_scripts', 'mps_enqueue_assets');

// =========================================================================
// HELPER 1: Block custom header/footer ONLY on exact parent pages
// =========================================================================
function mps_hide_custom_header_footer() {
    if ( ! is_page() ) {
        return false;
    }
    $current_page = get_queried_object();
    
    // ONLY blocking the exact parent pages: About Us and Academics.
    $target_slugs = array( 'about-us', 'academics' ); 
    
    if ( in_array( $current_page->post_name, $target_slugs ) ) {
        return true;
    }
    
    return false;
}

// =========================================================================
// HELPER 2: Dynamically highlight the correct menu item
// =========================================================================
function mps_is_active_menu($slugs) {
    if (!is_array($slugs)) $slugs = array($slugs);
    global $post;
    if (!$post) return false;
    
    if (in_array($post->post_name, $slugs)) return true;
    
    if ($post->post_parent > 0) {
        $ancestors = get_post_ancestors($post->ID);
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_post($ancestor_id);
            if (in_array($ancestor->post_name, $slugs)) return true;
        }
    }
    return false;
}

// =========================================================================
// 1. GLOBAL HEADER SHORTCODE
// =========================================================================
function mps_header_shortcode() {
    

    ob_start();
    ?>
    <style>
        .mps-global-header { 
            width: 100vw;
            margin-left: 50%;
            transform: translateX(-50%);
            background: #fff; 
            font-family: 'Poppins', sans-serif; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); 
            position: relative; 
            z-index: 9999; 
        }
        .mps-header-container { 
            max-width: 1300px; 
            margin: 0 auto; 
            padding: 15px 20px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        .mps-logo a { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            text-decoration: none; 
        }
        .mps-logo img { 
            height: 65px; 
            width: auto; 
            object-fit: contain; 
        }
        .mps-brand-text { 
            display: flex; 
            flex-direction: column; 
            line-height: 1.2; 
        }
        .mps-school-name { 
            font-size: 20px; 
            font-weight: 800; 
            color: #146c2e; 
            letter-spacing: -0.5px;
        }
        .mps-motto { 
            font-size: 12px; 
            font-style: italic; 
            color: #f5b700;
            font-weight: 600; 
            margin-top: 2px; 
        }
        
        .mps-nav ul { 
            list-style: none; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            gap: 5px; 
        }
        .mps-nav ul li { 
            position: relative; 
        }
        .mps-nav ul li a { 
            text-decoration: none; 
            color: #2d3748; 
            font-weight: 600; 
            font-size: 14px; 
            padding: 10px 18px; 
            border-radius: 25px; 
            transition: all 0.3s ease; 
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .mps-nav ul li a:hover, 
        .mps-nav ul li a.active { 
            background: #146c2e; 
            color: #fff; 
        }
        .mps-nav a.mps-parent { cursor: default; }
.mps-nav ul li.mps-open .dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        .mps-nav ul li a i {
            transition: transform 0.3s ease;
        }
        .mps-nav ul li:hover > a i {
            transform: rotate(180deg);
        }

        .mps-nav .dropdown { 
            position: absolute; 
            top: 100%; 
            margin-top: 10px;
            left: 0; 
            background: #fff; 
            box-shadow: 0 15px 30px rgba(0,0,0,0.1); 
            min-width: 240px; 
            border-radius: 12px; 
            padding: 10px 0; 
            z-index: 999; 
            flex-direction: column; 
            gap: 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            transition-delay: 0.15s; 
            border-top: 3px solid #f5b700;
        }
        
        .mps-nav .dropdown::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 0;
            width: 100%;
            height: 10px;
            background: transparent;
        }

        .mps-nav ul li:hover .dropdown { 
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition-delay: 0s;
        }
        
        .mps-nav .dropdown li a { 
            padding: 12px 24px; 
            border-radius: 0; 
            font-weight: 500; 
            font-size: 14px; 
            color: #4a5568;
        }
        .mps-nav .dropdown li a:hover { 
            background: #f0f7f2; 
            color: #146c2e; 
            padding-left: 28px;
        }
        
        @media (max-width: 992px) { 
            .mps-nav ul li.mps-open .dropdown { display: block; }
            .mps-header-container { 
                flex-direction: column; 
                gap: 20px; 
                padding: 20px;
            } 
            .mps-nav ul { 
                flex-wrap: wrap; 
                justify-content: center; 
            }
            .mps-nav .dropdown {
                position: static;
                box-shadow: none;
                border-top: none;
                background: #f8fafc;
                border-radius: 8px;
                margin-top: 5px;
                transition: none;
                opacity: 1;
                visibility: visible;
                transform: none;
                display: none;
            }
            .mps-nav ul li:hover .dropdown {
                display: block;
            }
        }
    </style>

    <header class="mps-global-header">
        <div class="mps-header-container">
            <div class="mps-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="https://via.placeholder.com/150x65/146c2e/ffffff?text=MPS+Logo" alt="Mukumbwe Primary School Logo">
                    <div class="mps-brand-text">
                        <span class="mps-school-name">MUKUMBWE<br>PRIMARY SCHOOL</span>
                        <span class="mps-motto">Learning &bull; Discipline &bull; Excellence</span>
                    </div>
                </a>
            </div>
            <nav class="mps-nav">
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>" class="<?php echo (is_front_page() || is_home()) ? 'active' : ''; ?>">Home</a></li>
                    <li>
                       <a class="mps-parent <?php echo mps_is_active_menu(array('about-us', 'headmistress-welcome', 'our-history', 'mission-vision')) ? 'active' : ''; ?>" tabindex="0" role="button" aria-haspopup="true">About Us <i class="fas fa-chevron-down" style="font-size:10px;"></i></a>
                        <ul class="dropdown">
                            <li><a href="<?php echo esc_url(home_url('/headmistress-welcome/')); ?>">Headmistress' Welcome</a></li>
                            <li><a href="<?php echo esc_url(home_url('/our-history/')); ?>">Our History</a></li>
                            <li><a href="<?php echo esc_url(home_url('/mission-vision/')); ?>">Mission & Values</a></li>
                        </ul>
                    </li>
                    <li>
                       <a class="mps-parent <?php echo mps_is_active_menu(array('academics', 'academics-section', 'curriculum-learning')) ? 'active' : ''; ?>" tabindex="0" role="button" aria-haspopup="true">Academics <i class="fas fa-chevron-down" style="font-size:10px;"></i></a>
                        <ul class="dropdown">
                            <li><a href="<?php echo esc_url(home_url('/academics-section/')); ?>">Academics Section</a></li>
                            <li><a href="<?php echo esc_url(home_url('/curriculum-learning/')); ?>">Curriculum & Learning</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(home_url('/admissions/')); ?>" class="<?php echo mps_is_active_menu(array('admissions', 'admission-information', 'fees')) ? 'active' : ''; ?>">Admissions <i class="fas fa-chevron-down" style="font-size:10px;"></i></a>
                        <ul class="dropdown">
                            <li><a href="<?php echo esc_url(home_url('/admission-information/')); ?>">Admission Info</a></li>
                            <li><a href="<?php echo esc_url(home_url('/fees/')); ?>">Fees</a></li>
                        </ul>
                    </li>
                    <li><a href="<?php echo esc_url(home_url('/news-events/')); ?>" class="<?php echo is_page('news-events') ? 'active' : ''; ?>">News & Events</a></li>
                    <li><a href="<?php echo esc_url(home_url('/gallery/')); ?>" class="<?php echo is_page('gallery') ? 'active' : ''; ?>">Gallery</a></li>
                    <li><a href="<?php echo esc_url(home_url('/contact-us/')); ?>" class="<?php echo is_page('contact-us') ? 'active' : ''; ?>">Contact Us</a></li>
                </ul>
            </nav>
        </div>
        <script>
document.addEventListener('click', function (e) {
    var label = e.target.closest('.mps-nav a.mps-parent');
    if (!label) return;
    if (window.matchMedia('(max-width: 992px)').matches) {
        e.preventDefault();
        var li = label.parentElement;
        document.querySelectorAll('.mps-nav li.mps-open').forEach(function (el) {
            if (el !== li) el.classList.remove('mps-open');
        });
        li.classList.toggle('mps-open');
    }
});
</script>
    </header>
    <?php
    return ob_get_clean();
}
add_shortcode('mps_header', 'mps_header_shortcode');

// =========================================================================
// 2. GLOBAL FOOTER SHORTCODE
// =========================================================================
function mps_footer_shortcode() {
   
    ob_start();
    ?>
    <style>
        .mps-global-footer { 
            width: 100vw;
            margin-left: 50%;
            transform: translateX(-50%);
            background: #146c2e; 
            color: #fff; 
            font-family: 'Poppins', sans-serif; 
            padding: 70px 0 0; 
        }
        .mps-footer-container { 
            max-width: 1300px; 
            margin: 0 auto; 
            padding: 0 20px; 
            display: grid; 
            grid-template-columns: 1.2fr 1fr 1fr 1.5fr;
            gap: 40px; 
            margin-bottom: 50px; 
        }
        
        .mps-footer-logo h3 { 
            font-size: 22px; 
            line-height: 1.2; 
            font-weight: 800; 
            margin-bottom: 5px;
        }
        .mps-footer-logo span { 
            font-size: 13px; 
            font-style: italic; 
            color: #f5b700; 
            font-weight: 500;
        }
        
        .mps-footer h4 { 
            font-size: 16px; 
            margin-bottom: 25px; 
            color: #f5b700; 
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .mps-footer ul { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }
        .mps-footer ul li { 
            margin-bottom: 14px; 
        }
        .mps-footer ul li a { 
            color: rgba(255,255,255,0.8); 
            font-size: 14px; 
            text-decoration: none; 
            transition: all 0.3s ease; 
            display: inline-block;
        }
        .mps-footer ul li a:hover { 
            color: #f5b700; 
            transform: translateX(5px);
        }
        
        .mps-footer-socials { 
            display: flex; 
            gap: 12px; 
        }
        .mps-social-static { 
            display: inline-flex; 
            justify-content: center; 
            align-items: center; 
            width: 42px; 
            height: 42px; 
            background: rgba(255,255,255,0.1); 
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50%; 
            color: #fff; 
            font-size: 16px;
            cursor: default; 
            transition: all 0.3s ease;
        }
        .mps-social-static:hover { 
            background: #f5b700; 
            color: #146c2e; 
            border-color: #f5b700;
            transform: translateY(-4px);
            box-shadow: 0 5px 15px rgba(245, 183, 0, 0.3);
        }
        
        .mps-footer-quote { 
            font-family: 'Great Vibes', cursive; 
            font-size: 36px; 
            color: #fff; 
            line-height: 1.3; 
            position: relative;
            padding-top: 20px;
        }
        .mps-footer-quote::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #f5b700;
            border-radius: 2px;
        }
        
        .mps-copyright { 
            background: #f5b700; 
            color: #146c2e; 
            padding: 18px 0; 
            font-size: 13px; 
            font-weight: 600; 
            width: 100%; 
        }
        .mps-copyright .mps-container { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            max-width: 1300px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        
        @media (max-width: 992px) { 
            .mps-footer-container { 
                grid-template-columns: 1fr 1fr; 
                gap: 40px;
            } 
            .mps-footer-quote {
                grid-column: 1 / -1;
                text-align: center;
                padding-top: 30px;
                border-top: 1px solid rgba(255,255,255,0.1);
            }
            .mps-footer-quote::before {
                left: 50%;
                transform: translateX(-50%);
            }
            .mps-copyright .mps-container { 
                flex-direction: column; 
                text-align: center; 
                gap: 10px; 
            } 
        }
        @media (max-width: 576px) {
            .mps-footer-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .mps-footer-socials {
                justify-content: center;
            }
            .mps-footer ul li a:hover {
                transform: translateX(0);
            }
        }
    </style>

    <footer class="mps-global-footer mps-footer">
        <div class="mps-footer-container">
            <div>
                <div class="mps-footer-logo">
                    <h3>MUKUMBWE<br>PRIMARY SCHOOL</h3>
                    <span>Learning &bull; Discipline &bull; Excellence</span>
                </div>
            </div>
            
            <div>
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                    <li><a href="<?php echo esc_url(home_url('/fees/')); ?>">Fees Structure</a></li>
                    <li><a href="<?php echo esc_url(home_url('/curriculum-learning/')); ?>">Curriculum</a></li>
                    <li><a href="<?php echo esc_url(home_url('/our-history/')); ?>">Our History</a></li>
                </ul>
            </div>
            
            <div>
                <h4>Connect With Us</h4>
                <div class="mps-footer-socials">
                    <span class="mps-social-static" title="Facebook"><i class="fab fa-facebook-f"></i></span>
                    <span class="mps-social-static" title="YouTube"><i class="fab fa-youtube"></i></span>
                    <span class="mps-social-static" title="Instagram"><i class="fab fa-instagram"></i></span>
                </div>
            </div>
            
            <div>
                <div class="mps-footer-quote">
                    "Together for<br>a brighter future"
                </div>
            </div>
        </div>
        
        <div class="mps-copyright">
            <div class="mps-container">
                <span>&copy; <?php echo date('Y'); ?> Mukumbwe Primary School. All Rights Reserved.</span>
                <span>Nurturing Minds, Building Futures.</span>
            </div>
        </div>
    </footer>
    <?php
    return ob_get_clean();
}
add_shortcode('mps_footer', 'mps_footer_shortcode');
// Parent labels are never visited — send anyone who types the URL to the first sub-page
add_action('template_redirect', function () {
    if ( is_page('about-us') ) {
        wp_safe_redirect( home_url('/headmistress-welcome/'), 301 );
        exit;
    }
    if ( is_page('academics') ) {
        wp_safe_redirect( home_url('/academics-section/'), 301 );
        exit;
    }
});
// Dynamic site URL shortcode for Elementor HTML widgets
function mps_url_shortcode($atts) {
    $atts = shortcode_atts(array('path' => '/'), $atts);
    return esc_url(home_url($atts['path']));
}
add_shortcode('mps_url', 'mps_url_shortcode');