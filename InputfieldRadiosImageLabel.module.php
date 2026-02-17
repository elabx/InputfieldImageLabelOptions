<?php
namespace ProcessWire;

/**
 * InputfieldRadiosImageLabel
 *
 * Renders radio buttons using images or styled labels, following the technique:
 * https://stackoverflow.com/a/17541916/3612981
 *
 * Works as a standalone Inputfield for FieldtypeOptions — no custom Fieldtype needed.
 * Select this inputfield on the "Input" tab of any Options field.
 *
 */

class InputfieldRadiosImageLabel extends InputfieldRadios
{

	public static function getModuleInfo()
	{
		return array(
			'title' => 'Inputfield Radios Image Label',
			'version' => 101,
			'summary' => 'Radios that use images/labels instead of standard radio buttons, suitable for FieldtypeOptions.',
			'requires' => 'ProcessWire>=3.0.0',
		);
	}

	public function init() {
		$this->set('optionImages', '');
		$this->set('optionImageDesktopWidth', 150);
		$this->set('optionImageMobileWidth', 100);
		$this->set('optionImageAspectRatio', '');
		$this->set('optionImageShowLabel', 0);
		parent::init();
	}

	public function ___getConfigInputfields()
	{
		$inputfields = parent::___getConfigInputfields();

		/** @var InputfieldTextarea $f */
		$f = $this->wire('modules')->get('InputfieldTextarea');
		$f->attr('name', 'optionImages');
		$f->label = $this->_('Option Images');
		$f->description = $this->_('Enter one per line in the format: option_id=image_url (or option_value=image_url)');
		$f->notes = $this->_('Example: \n1=/site/assets/red.png\nmy_val=/site/assets/blue.png');
		$f->value = $this->optionImages;
		$inputfields->add($f);

		/** @var InputfieldInteger $f */
		$f = $this->wire('modules')->get('InputfieldInteger');
		$f->attr('name', 'optionImageDesktopWidth');
		$f->label = $this->_('Desktop Image Width');
		$f->description = $this->_('Width in pixels for images on desktop screens. Default is 150px.');
		$f->value = $this->optionImageDesktopWidth;
		$f->attr('min', 1);
		$inputfields->add($f);

		/** @var InputfieldInteger $f */
		$f = $this->wire('modules')->get('InputfieldInteger');
		$f->attr('name', 'optionImageMobileWidth');
		$f->label = $this->_('Mobile Image Width');
		$f->description = $this->_('Width in pixels for images on mobile screens. Default is 100px.');
		$f->value = $this->optionImageMobileWidth;
		$f->attr('min', 1);
		$inputfields->add($f);

		/** @var InputfieldText $f */
		$f = $this->wire('modules')->get('InputfieldText');
		$f->attr('name', 'optionImageAspectRatio');
		$f->label = $this->_('Image Aspect Ratio');
		$f->description = $this->_('Aspect ratio for images in format "width:height" (e.g., "16:9", "1:1", "4:3"). Leave empty for no aspect ratio constraint.');
		$f->value = $this->optionImageAspectRatio;
		$f->notes = $this->_('Examples: 16:9, 1:1, 4:3, 3:2');
		$inputfields->add($f);

		/** @var InputfieldCheckbox $f */
		$f = $this->wire('modules')->get('InputfieldCheckbox');
		$f->attr('name', 'optionImageShowLabel');
		$f->label = $this->_('Show Label Below Image');
		$f->description = $this->_('If checked, the option label text will be displayed below the image.');
		$f->attr('value', 1);
		$f->checked = $this->optionImageShowLabel ? 'checked' : '';
		$inputfields->add($f);

		return $inputfields;
	}

	public function render()
	{
		// Get configuration values
		$desktopWidth = (int)$this->optionImageDesktopWidth ?: 150;
		$mobileWidth = (int)$this->optionImageMobileWidth ?: 100;
		$aspectRatio = $this->optionImageAspectRatio ? trim($this->optionImageAspectRatio) : '';
		$showLabel = (bool)$this->optionImageShowLabel;

		// Build CSS custom properties for responsive widths (namespaced with --inputfield-image-label-options-)
		$cssVars = "--inputfield-image-label-options-desktop-width: {$desktopWidth}px; --inputfield-image-label-options-mobile-width: {$mobileWidth}px;";
		if ($aspectRatio) {
			// Parse aspect ratio (e.g., "16:9" -> 16/9 = 1.777...)
			if (preg_match('/^(\d+(?:\.\d+)?):(\d+(?:\.\d+)?)$/', $aspectRatio, $matches)) {
				$ratio = (float)$matches[1] / (float)$matches[2];
				$cssVars .= " --inputfield-image-label-options-aspect-ratio: {$ratio};";
			}
		}

		$out = "<div class='InputfieldRadiosImageLabelWrapper' style='$cssVars'>";

		// Parse the optionImages configuration
		$imageMap = array();
		if ($this->optionImages) {
			$lines = explode("\n", $this->optionImages);
			foreach ($lines as $line) {
				if (strpos($line, '=') === false)
					continue;
				list($k, $v) = explode('=', $line, 2);
				$imageMap[trim($k)] = trim($v);
			}
		}

		foreach ($this->getOptions() as $key => $value) {
			$checked = ($key == $this->value) ? " checked='checked'" : "";
			$id = $this->id . "_" . $this->wire('sanitizer')->name($key);
			$name = $this->name;

			$textLabel = $this->wire('sanitizer')->entities($value);
			$hasImage = isset($imageMap[$key]);

			// If an image is defined for this option key, use it
			if ($hasImage) {
				$imgUrl = $this->wire('sanitizer')->url($imageMap[$key]);

				// Determine wrapper class - apply aspect ratio to image wrapper
				$wrapperClass = 'image-wrapper';
				if ($aspectRatio) {
					$wrapperClass .= ' has-aspect-ratio';
				}

				$label = "<span class='$wrapperClass'><img src='$imgUrl' alt='$textLabel' class='image-label-img' /></span>";

				// Add label text below image if configured
				if ($showLabel) {
					$label .= "<span class='image-label-text'>$textLabel</span>";
				}
			} else {
				// Fallback to text label (entity-encoded for safety)
				$label = $textLabel;
			}

			$out .= "<label for='$id' class='image-label-option'>";
			$out .= "<input type='radio' name='$name' id='$id' value='$key'$checked />";
			$out .= "<span class='content'>$label</span>";
			$out .= "</label>";
		}

		$out .= "</div>";
		return $out;
	}
}
