slider-shortcode
================

Developer: Amir Entezaralmahdi - UBC Arts ISIT

Requirements: Works only on UBC Collab theme

Version: 1.0

Description:

This UBC plugin allows users to place a slider on any page as a shortcode

Instruction:

Place the following code on any page or post, and you will have a feature slider:

[slider]

Parameter list:
  
  height - (default value: '330') - This parameter will change the height of your slider image to the entered value
  
  category - (default value: '0' [it will display all posts]) - You can choose what post category to be displayed, by simply entering category slug or ID
  
  slider_margin - (default value: 'false') - By setting this parameter to 'true' the 15px margin around the slider will be removed
  
  lookandfeel - (default value: 'standard') - You can select from the following look and feel of sliders: {standard, blank, multi, transparent, basic-sliding}
  
  maxslides - (default value: '10') - This is the number of slides to be shown within the posts in the specified category, or all categories if a category is not selected
  
  read_more_check - (default value: 'false') - If you set this to 'true' it will add a read-more text at the end of the text on the slider
  
  read_more_text - (default value: 'Read more') - If read_more_check is set to 'true' this value will be displayed at the end of the text on the slider
  
  remove_link_to - (default value: 'false') - By setting this parameter to 'true' the anchor link to the slider image will be removed
  
  
Usage example with default values:

[slider height='330' category='0' slider_margin='false' lookandfeel='standard' maxslides='10' read_more_check='false' read_more_text='Read more' remove_link_to='false']
