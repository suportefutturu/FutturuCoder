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
>
    <div class="scp-popup">
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
                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ?: $title ); ?>" />
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
                    <button class="scp-button scp-close-btn-action" type="button">
                        <?php echo esc_html( $button_text ); ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
