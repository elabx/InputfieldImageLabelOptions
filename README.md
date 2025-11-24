# InputfieldRadiosImageLabel

A ProcessWire Inputfield module that extends `InputfieldRadios` to render radio buttons as selectable images or styled labels.

## Description

This module implements the technique described in [this Stack Overflow answer](https://stackoverflow.com/a/17541916/3612981). It hides the standard radio button and allows you to style the label content (e.g., an image) to act as the clickable selector.

## Installation

1. Copy the `InputfieldRadiosImageLabel` directory to your ProcessWire `site/modules/` directory.
2. Login to the ProcessWire admin.
3. Go to Modules > Refresh.
4. Click "Install" next to "Inputfield Radios Image Label".

## Usage with FieldtypeOptions

1. Create or edit a field of type **Options** (`FieldtypeOptions`).
2. In the **Details** tab, define your options. 
   - If you want to use images, you can include HTML `<img>` tags in the option labels.
   - Example Options:
     ```
     small=<img src='/site/templates/img/small.png' alt='Small'>
     medium=<img src='/site/templates/img/medium.png' alt='Medium'>
     large=<img src='/site/templates/img/large.png' alt='Large'>
     ```
3. In the **Input** tab, change the **Inputfield Type** to **Inputfield Radios Image Label**.
4. **Important:** If you are using HTML (like `<img>` tags) in your option labels, you must set **Entity Encode** to **No** (or "None") in the Input tab settings for this field. Otherwise, the HTML tags will be escaped and visible as text.

## Customization

The module includes a default CSS file `InputfieldRadiosImageLabel.css` which handles the hiding of radio buttons and the selection border. You can override these styles in your own admin theme CSS or by modifying the module's CSS.
