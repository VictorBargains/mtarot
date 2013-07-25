#Michael Tarot Card Plugin#
This is a WordPress plugin which uses custom post types to represent a deck of cards, as well as layouts which allow those cards to be randomly dealt with certain options.

##Custom Post Types##
The proper functioning of this plugin requires that two custom post types be created manually, with specific settings.

###tarot-card###
A tarot-card post represents a card in a deck.
The deck is determined by the Category of the post.
The tarot card's slug for use in layout shortcodes is in the custom field __tarot-slug__.
Multiple interpretations of the cards can be added, for both negative and positive polarities. These will be read by the layouts when dealing a card into a slot, and by any widgets which simulate card dealings.

###tarot-layout###
A tarot-layout post should include some number of slots, into which tarot-card posts are fetched randomly and then dealt, being rendered as card images with descriptions.

###TODO:###
 * Identify required parameters for custom post types (including rewrite rules)
 * Implement custom post types automatically when plugin is registered

##Widgets##
None of these widgets exist in functional form yet -- only a placeholder has been made.

###TODO:###
Make all these widgets function:

###tarot-form###
simulates or calls the [tarot-form] shortcode with appropriate attributes specified by widget configuration options.

###tarot-card###
displays a card based on specific criteria specified by widget configuration options.

###card-of-the-day###
displays a specific card+polarity+interpretation tuple which is randomly selected every day from among possible tarot-card combinations. appearance should be customizable and similar to the tarot-card widget. 
