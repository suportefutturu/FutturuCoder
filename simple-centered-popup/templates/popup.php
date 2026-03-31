<?php
/**
 * Popup template file
 *
 * @package Simple_Centered_Popup
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div 
    id="scp-popup-overlay" 
    class="scp-overlay scp-animation-<?php echo esc_attr( $animation ); ?>"
    role="dialog"
    aria-modal="true"
    aria-labelledby="scp-popup-title"
    style="--scp-overlay-opacity: <?php echo esc_attr( $overlay_opacity ); ?>;"
>
    <div 
        class="scp-popup"
        style="
            max-width: <?php echo esc_attr( $max_width ); ?>;
            background-color: <?php echo esc_attr( $background_color ); ?>;
            border-radius: <?php echo esc_attr( $border_radius ); ?>;
            --scp-animation-duration: <?php echo esc_attr( $animation_duration ); ?>;
            --scp-button-color: <?php echo esc_attr( $button_color ); ?>;
        "
    >
        <button 
            class="scp-close-btn" 
            aria-label="<?php esc_attr_e( 'Close popup', 'simple-centered-popup' ); ?>"
            type="button"
        >
            <span aria-hidden="true">&times;</span>
        </button>
        
        <?php if ( ! empty( $title ) ) : ?>
            <h2 id="scp-popup-title" class="scp-popup-title">
                <?php echo esc_html( $title ); ?>
            </h2>
        <?php endif; ?>
        
        <?php if ( ! empty( $image_url ) ) : ?>
            <div class="scp-popup-image">
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
            </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $video_embed ) ) : ?>
            <div class="scp-popup-video">
                <?php echo $video_embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - Already sanitized with wp_kses_post ?>
            </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $content ) ) : ?>
            <div class="scp-popup-content">
                <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - Already sanitized with wp_kses_post ?>
            </div>
        <?php endif; ?>
        
        <?php if ( ! empty( $button_text ) ) : ?>
            <div class="scp-popup-footer">
                <?php if ( ! empty( $button_url ) ) : ?>
                    <a 
                        href="<?php echo esc_url( $button_url ); ?>" 
                        class="scp-button"
                        <?php echo $button_new_tab ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
                    >
                        <?php echo esc_html( $button_text ); ?>
                    </a>
                <?php else : ?>
                    <button class="scp-button scp-close-btn" type="button">
                        <?php echo esc_html( $button_text ); ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
