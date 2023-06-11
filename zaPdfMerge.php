<?php
/*
Plugin Name: Za Pdf Merge
Plugin URI: https://wordpress.org
Description: Merge pdf with our defind template pdf and create a quotaion post in admin dashboard. Use [za-pdf-merge] shortcode for frontend form
Version: 1.0.0
Author: Zohaib Ahmad
Author URI: https://your-website.com/
License: GPLv2 or later
Text Domain: za-pdf-merge
 */

// Plugin code starts here
function quotations_register_custom_post_type()
{
    $labels = array(
        'name' => 'Quotations',
        'singular_name' => 'Quotation',
        'menu_name' => 'Quotations',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Quotation',
        'edit' => 'Edit',
        'edit_item' => 'Edit Quotation',
        'new_item' => 'New Quotation',
        'view' => 'View',
        'view_item' => 'View Quotation',
        'search_items' => 'Search Quotations',
        'not_found' => 'No quotations found',
        'not_found_in_trash' => 'No quotations found in trash',
        'parent' => 'Parent Quotation',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'quotation'),
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title'),
        'register_meta_box_cb' => 'quotations_add_custom_fields',
    );

    register_post_type('quotation', $args);
}
add_action('init', 'quotations_register_custom_post_type');

function quotations_add_custom_fields()
{
    add_meta_box('quotations_fields', 'Quotation Details', 'quotations_render_custom_fields', 'quotation', 'normal', 'default');
}

function quotations_render_custom_fields($post)
{
    // Retrieve the existing values of custom fields
    $name = get_post_meta($post->ID, 'quotation_name', true);
    $date = get_post_meta($post->ID, 'quotation_date', true);
    $email = get_post_meta($post->ID, 'quotation_email', true);
    $pdf_file = get_post_meta($post->ID, 'quotation_pdf_file', true);

    // Output the HTML for custom fields
    ?>
    <p>
        <label for="quotation_name">Name:</label>
        <input type="text" id="quotation_name" name="quotation_name" value="<?php echo esc_attr($name); ?>" />
    </p>
    <p>
        <label for="quotation_date">Date:</label>
        <input type="text" id="quotation_date" name="quotation_date" value="<?php echo esc_attr($date); ?>" />
    </p>
    <p>
        <label for="quotation_email">Email:</label>
        <input type="email" id="quotation_email" name="quotation_email" value="<?php echo esc_attr($email); ?>" />
    </p>
    <p>
        <label for="quotation_pdf_file">PDF File:</label>
        <input type="text" id="quotation_pdf_file" name="quotation_pdf_file" value="<?php echo esc_attr($pdf_file); ?>" />
    </p>
    <?php
}

function quotations_save_custom_fields($post_id)
{
    // Save the custom field values
    if (isset($_POST['quotation_name'])) {
        update_post_meta($post_id, 'quotation_name', sanitize_text_field($_POST['quotation_name']));
    }
    if (isset($_POST['quotation_date'])) {
        update_post_meta($post_id, 'quotation_date', sanitize_text_field($_POST['quotation_date']));
    }
    if (isset($_POST['quotation_email'])) {
        update_post_meta($post_id, 'quotation_email', sanitize_email($_POST['quotation_email']));
    }
    if (isset($_POST['quotation_pdf_file'])) {
        update_post_meta($post_id, 'quotation_pdf_file', sanitize_text_field($_POST['quotation_pdf_file']));
    }
}
add_action('save_post_quotation', 'quotations_save_custom_fields');


function display_quotation_custom_fields($content) {
    // Check if the post is of the "quotation" post type
    if (is_singular('quotation')) {
        $post_id = get_the_ID();

        // Get the custom field values
        $name = get_post_meta($post_id, 'quotation_name', true);
        $date = get_post_meta($post_id, 'quotation_date', true);
        $email = get_post_meta($post_id, 'quotation_email', true);
        $pdf_file = get_post_meta($post_id, 'quotation_pdf_file', true);

        // Create the custom fields HTML
        $custom_fields_html = '
        <p>
            <strong>Name:</strong> ' . esc_html($name) . '
        </p>
        <p>
            <strong>Date:</strong> ' . esc_html($date) . '
        </p>
        <p>
            <strong>Email:</strong> ' . esc_html($email) . '
        </p>
        <p>
            <a href="' . esc_html($pdf_file) . '" class="button">Download Quotaion</a>
        </p>';

        // Append the custom fields HTML to the post content
        $content .= $custom_fields_html;
    }

    return $content;
}
add_filter('the_content', 'display_quotation_custom_fields');


function quotations_form_shortcode()
{
    ob_start();

    // Display the form HTML
    ?>
    <style>
        .za-card {
            background-color: #f5f5f5;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: 0 auto;
        }
        .za-card p {
            margin-bottom: 0.75em;
        }
        .za-card label {
            display: block;
            margin-bottom: 5px;
        }

        .za-card input[type="text"],
        .za-card input[type="email"],
        .za-card input[type="file"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
        }
    </style>

    <form class="quotations-form za-card" id="quotations-form" method="post" enctype="multipart/form-data">
        <p>
            <label for="quotation_name">Name:</label>
            <input type="text" id="quotation_name" name="quotation_name" required />
        </p>
        <p>
            <label for="quotation_email">Email:</label>
            <input type="email" id="quotation_email" name="quotation_email" required />
        </p>
        <p>
            <label for="quotation_pdf_file">PDF File:</label>
            <input type="file" id="quotation_pdf_file" name="quotation_pdf_file" multiple="multiple" accept=".pdf" required />
        </p>
        <p>
			<button type="submit" name="quotations_submit">Submit <img src="https://stagingspace.co/wp-content/uploads/2023/06/ZZ5H.gif" class="za-preloader" style="width:25px;display: none;"></button>
        </p>
    </form>



    <div id="quotations-message"></div>
    <script src="https://unpkg.com/pdf-lib@1.4.0"></script>
    <script src="https://unpkg.com/downloadjs@1.4.7"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous" ></script>
    <script>
        const { PDFDocument } = PDFLib;

        window.arrayOfPdf = [];

        window.addEventListener("DOMContentLoaded", loadPdf, false);

        function loadPdf() {
            var url = '//stagingspace.co/wp-content/uploads/2023/06/template_start.pdf'; // Replace with the  
			
  fetch(url)
                .then(function(response) {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Network response was not ok.');
                })
                .then(function(blob) {
                    var reader = new FileReader();

                    reader.onloadend = function() {
                        var data = reader.result;
						console.log(data);
                        window.arrayOfPdf.push({
                            bytes: new Uint8Array(data),
                            name: 'file.pdf',
                        });
                    };

                    reader.readAsArrayBuffer(blob);
                })
                .catch(function(error) {
                    console.log('Error:', error);
                }); 
        }
		
		
        function loadlastpage() {
            var url = '//stagingspace.co/wp-content/uploads/2023/06/template_end.pdf'; // Replace with the  
			
  fetch(url)
                .then(function(response) {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Network response was not ok.');
                })
                .then(function(blob) {
                    var reader = new FileReader();

                    reader.onloadend = function() {
                        var data = reader.result;
						console.log(data);
                        window.arrayOfPdf.push({
                            bytes: new Uint8Array(data),
                            name: 'template_end.pdf',
                        });
						
	                	joinPdf();
                    };

                    reader.readAsArrayBuffer(blob);
	  
                })
                .catch(function(error) {
                    console.log('Error:', error);
                }); 
        }



        var input = document.getElementById("quotation_pdf_file");

        input.addEventListener("change", openfile, false);

        function openfile(evt) {
            var files = input.files;
            fileData = new Blob([files[0]]);
            // Pass getBuffer to promise.
            var promise = new Promise(getBuffer(fileData));
            promise
                .then(function (data) {
                    window.arrayOfPdf.push({
                        bytes: data,
                        name: files[0].name,
                    });
                })
                .catch(function (err) {
                    console.log("Error: ", err);
                });
        }

        function getBuffer(fileData) {
            return function (resolve) {
                var reader = new FileReader();
                reader.readAsArrayBuffer(fileData);
                reader.onload = function () {
                    var arrayBuffer = reader.result;
                    var bytes = new Uint8Array(arrayBuffer);
                    resolve(bytes);
                };
            };
        }

        // Create a new FormData object
        var formData;

        jQuery(document).ready(function ($) {
            $(".quotations-form").submit(function (e) {
                e.preventDefault();
				
				$(".za-preloader").show();
                // Create a new FormData object
                formData = new FormData(this);
				loadlastpage();
            });
        });

        async function joinPdf() {
			
			
            const mergedPdf = await PDFDocument.create();
			console.log(window.arrayOfPdf);
			
            for (let document of window.arrayOfPdf) {
                document = await PDFDocument.load(document.bytes);
                const copiedPages = await mergedPdf.copyPages(
                    document,
                    document.getPageIndices()
                );
                copiedPages.forEach((page) => mergedPdf.addPage(page));
            }
            var pdfBytes = await mergedPdf.save();

            // Append the PDF data to the FormData object
            formData.append("pdfData", new Blob([pdfBytes], { type: "application/pdf" }));
            formData.append("action", 'upload_merged_pdf');

            // Send an AJAX request to the PHP script
            $.ajax({
                url: "/wp-admin/admin-ajax.php", // Update this with the correct path to your PHP script
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
					$(".za-preloader").hide();
                    alert('Quotation saved successfully');
                    // location.reload();
                },
                error: function (xhr, status, error) {
                    console.log("Error saving file:", error);
                }
            });
        }

    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('quotations_form', 'quotations_form_shortcode');

function upload_merged_pdf()
{
    if (isset($_FILES['pdfData'])) {
        $pdf_file = $_FILES['pdfData'];

        // Path to the destination file in the target folder
        $destination_file = wp_upload_dir()['path'] . '/quotaion' . '_' . time() . '.pdf';

        // Move the uploaded PDF file to the target folder
        if (move_uploaded_file($pdf_file['tmp_name'], $destination_file)) {
            // Create an attachment post
            $attachment = array(
                'post_mime_type' => 'application/pdf',
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit',
            );

            $attachment_id = wp_insert_attachment($attachment, $destination_file);

            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            // Generate attachment metadata
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $destination_file);
            wp_update_attachment_metadata($attachment_id, $attachment_data);

            // Create a new quotation post
            $name = isset($_POST['quotation_name']) ? sanitize_text_field($_POST['quotation_name']) : '';
            $date = isset($_POST['quotation_date']) ? sanitize_text_field($_POST['quotation_date']) : '';
            $email = isset($_POST['quotation_email']) ? sanitize_email($_POST['quotation_email']) : '';

            // Create a new quotation post
            $post_data = array(
                'post_title' => 'New Quotation - ' . $name,
                'post_type' => 'quotation',
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'meta_input' => array(
                    'quotation_name' => $name,
                    'quotation_date' => date('Y-m-d'),
                    'quotation_email' => $email,
                    'quotation_pdf_file' => wp_get_attachment_url($attachment_id),
                ),
            );

            $post_id = wp_insert_post($post_data);

            // Return the attachment ID in the response
            wp_send_json_success(array('attachment_id' => $attachment_id, 'post_id' => $post_id, 'post_link' => get_permalink($post_id)));
        } else {
            // File move failed, return error response
            wp_send_json_error(array('message' => 'File move failed'));
        }
    } else {
        // PDF file not found in the request, return error response
        wp_send_json_error(array('message' => 'PDF file not found in the request'));
    }
}

add_action('wp_ajax_upload_merged_pdf', 'upload_merged_pdf');
add_action('wp_ajax_nopriv_upload_merged_pdf', 'upload_merged_pdf');
