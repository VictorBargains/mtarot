#Michael Tarot Card Plugin#
This is a WordPress plugin which uses custom post types to represent a deck of tarot cards, as well as layouts which allow those cards to be randomly dealt with certain options. It was built for the website http://themichaelteaching.com to represent an online version of a deck of tarot cards being developed. When the physical deck of cards was manufactured, a second site, http://michaelcards.com was launched where the tarot plugin could have more of a prominent feature on the site. 

##Branches##
The master branch of this repo is outdated. Since initial creation, it has been modified separately on each of the two sites, outside of a strict lineage of git commits. The current runtime versions of each site have been merged into the `michaelcards` and `themichaelteaching` branches, leaving for the future the task of properly separating their customizations and merging the commonalities back into master.

##Custom Post Types##
The proper functioning of this plugin requires that two custom post types be created separately, one for cards and one for layouts. The custom post types to use can be specified in the tarot deck options.

###TODO:###
 - Implement custom post types automatically when plugin is registered

##Tarot Cards##
 - A tarot-card post represents a card in a deck.
 - The deck is determined by the Category of the post, allowing multiple decks.
 - The tarot card's slug for use in layout shortcodes is in the custom field __tarot-slug__, allowing filenames for images to be different from the post slug representing the card.
 - Multiple interpretations (text descriptions) of the cards can be added, for both negative and positive polarities. These will be read by the layouts when dealing a card into a slot, and by any widgets which simulate card dealings. 

##Tarot Layouts##
A layout is a fixed order in which cards are dealt to represent specific meanings. Different layouts have different numbers of slots where cards are dealt, each with names and descriptions. A tarot-layout post should include some number of slots, into which tarot-card posts are fetched randomly and then dealt, being rendered as card images with text to put the card in context as dealt.

###Asking A Question###
Using the tarot deck involves asking a question by filling out a text box and submitting the form, after selecting a layout to use. If passed a question in the query, a layout will deal cards randomly into its slots rendering a unique result for that question.

##Shortcodes##
Tarot cards can be dealt face up with a `[tcard]` shortcode, which accepts options for polarity and description, or tarot-slug or post ID to specify an individual card. Tarot cards otherwise are dealt using the `[tarot-card]` shortcode, which allows filters by category or taxonomy, polarity, etc. Additionally, it's possible to specify a name and description of the meaning of that particular slot in a layout where that card is being dealt. This allows layouts where some slots will only randomly select from a subset of the deck when dealing.

##Options##
Many settings can be changed in the dashboard, such as the text of labels as well as the card backs and the directory from which the card front images are pulled.

##Widgets##

###Card of the Day###
This widget connects to the daily card feature, which selects a new card each day which can be overridden or reset from the options panel. Daily cards are not repeated until the deck is cycled through. When each card has been featured once as the daily card, the counter is reset and each card is randomly eligible again until the next reset.
