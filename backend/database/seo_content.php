<?php
/**
 * Website copy from "BB_Caterers_SEO_Content" (SEO content document).
 * install.php applies it ONCE to an existing database (tracked by the
 * setting seo_content_version), so later edits made in the admin panel are kept.
 * Fresh installs get the same text from the seed rows in schema.sql.
 * A database already on an older version only receives the later 'patches'.
 * Paragraph text may contain **keyword** — shown in bold on the website.
 */
return [
  'version' => 2,
  'patches' => [
    // v2: keywords highlighted in the SEO document (bold)
    2 => ['settings' => [
      'about_text' => 'Bharatha Bhojanam means “the food of Bharat”. BB Caterers is a **Fontana, CA caterer** bringing generations-old recipes to your table: slow-ground podis, crisp dosas off the tawa, and festive sweets made the way our grandmothers made them.',
      'about_text_2' => 'As an **Indian food catering** service focused on **South Indian catering**, we cook every menu fresh, serve it with warmth and design it around your celebration, whether it is a wedding, a birthday party or a corporate event.',
      'services_intro' => 'Discover **Indian catering services** built around South Indian favourites: live dosa stations, snacks, podi varieties, festive sweets and chaat, all prepared fresh for your celebration in Fontana, CA.',
    ]],
  ],
  'settings' => [
    'meta_title' => 'Indian Catering Services in Fontana, CA | BB Caterers',
    'meta_description' => 'BB Caterers offers authentic Indian catering in Fontana, CA: live dosa stations, chaat, snacks and sweets for weddings, parties and corporate events.',
    'logo_alt' => 'BB Caterers (Bharatha Bhojanam) – Indian catering services in Fontana, CA',
    'about_text' => 'Bharatha Bhojanam means “the food of Bharat”. BB Caterers is a **Fontana, CA caterer** bringing generations-old recipes to your table: slow-ground podis, crisp dosas off the tawa, and festive sweets made the way our grandmothers made them.',
    'about_text_2' => 'As an **Indian food catering** service focused on **South Indian catering**, we cook every menu fresh, serve it with warmth and design it around your celebration, whether it is a wedding, a birthday party or a corporate event.',
    'services_title' => 'South Indian Flavours, Made Fresh for Your Celebration',
    'services_intro' => 'Discover **Indian catering services** built around South Indian favourites: live dosa stations, snacks, podi varieties, festive sweets and chaat, all prepared fresh for your celebration in Fontana, CA.',
    'services_intro_2' => 'Whether you\'re planning a family function or a large gathering, we customise every menu around your guests, with vegetarian and South Indian options available.',
    'gallery_eyebrow' => 'Our Gallery',
    'gallery_title' => 'A Taste of Our Catering, Served Fresh',
    'highlight_text' => 'Indian catering built on traditional recipes, quality ingredients and freshly prepared food.',
    'highlight_image_alt' => 'Plated Indian feast with a colourful variety of traditional dishes',
    'events_title' => 'Made for *moments* worth *celebrating*.',
    'faq_enabled' => '1',
    'faq_eyebrow' => 'Frequently Asked Questions',
    'faq_title' => 'Catering Questions, Answered.',
    'faq_text' => 'Planning a wedding, birthday party or corporate event? Find answers about our menus, live stations and quotes below, or contact our team for help.',
    'contact_text' => 'Tell us about your wedding, party or corporate event and we’ll craft a catering menu just for you in Fontana, CA.',
    'contact_event_types' => 'Wedding / Reception
Birthday / Private Party
Corporate Event
Festival / Special Occasion
Other',
    'footer_about' => 'Authentic Indian catering in Fontana, CA, featuring live dosa stations, chaat, festive sweets and traditional South Indian favourites for weddings, parties and special celebrations.',
    'float_whatsapp_label' => 'Chat with BB Caterers on WhatsApp',
    'float_instagram_label' => 'BB Caterers on Instagram',
    'float_call_label' => 'Call BB Caterers on 840 239 9450',
  ],
  'rows' => [
    'hero_slides' => [
      1 => ['title' => 'Authentic Indian Catering. Crafted for Every Celebration', 'subtitle' => 'Traditional Indian dishes, live food stations and festive favourites, freshly prepared for weddings, parties and corporate events.'],
      2 => ['subtitle' => 'Bring the excitement of a live dosa station and freshly prepared snacks to your celebration, cooked fresh and served hot to your guests.'],
      3 => ['title' => 'Traditional Indian Sweets, Snacks & Festive Feasts', 'subtitle' => 'Handcrafted festive treats and traditional menus made the way they always have been, from Diwali sweets to full festive feasts.'],
    ],
    'services' => [
      1 => ['description' => 'Fresh dosas made live and served hot with chutneys and sambar.', 'alt_text' => 'Masala dosa with chutneys and sambar – live dosa station catering'],
      2 => ['description' => 'Fresh South Indian snacks and savouries, prepared at your event.', 'alt_text' => 'Brass bowls of traditional South Indian snacks and savouries'],
      3 => ['description' => 'Traditional podis made to complement idli, dosa and rice.', 'alt_text' => 'Brass bowls of colourful podi and spice powders'],
      4 => ['description' => 'Handcrafted Indian sweets and festive favourites for every celebration.', 'alt_text' => 'Laddus and murukku in brass bowls – Indian sweets and snacks for Diwali'],
      5 => ['description' => 'Fresh pani puri, bhel puri and more at a live chaat counter.', 'alt_text' => 'Colourful bowl of chaat – live chaat catering'],
    ],
    'gallery' => [
      1 => ['alt_text' => 'Crisp masala dosa served with chutneys and sambar – dosa catering by BB Caterers'],
      2 => ['alt_text' => 'Live chaat with bhel and pani puri – chaat catering for parties and weddings'],
      3 => ['alt_text' => 'Brass bowls of podi and spice powders – traditional podi varieties'],
      4 => ['alt_text' => 'Medu vada on a banana leaf with chutney – South Indian snacks catering'],
      5 => ['alt_text' => 'Laddus and murukku in brass bowls – Indian sweets catering for festivals'],
      7 => ['alt_text' => 'Traditional South Indian meal on a banana leaf with rice, papad and sides – vegetarian catering'],
      8 => ['alt_text' => 'Wedding buffet spread in brass serving vessels with marigold garlands – wedding catering in Fontana, CA'],
    ],
    'events' => [
      1 => ['description' => 'Wedding catering with traditional menus, live stations and festive sweets, planned around your celebration and guests.', 'alt_text' => 'Wedding catering buffet with brass serving vessels and marigold garlands'],
      2 => ['description' => 'Birthday and private party catering with live snacks, chaat and traditional South Indian favourites.', 'alt_text' => 'Traditional South Indian meal served on a banana leaf for a private celebration'],
      3 => ['description' => 'Corporate catering with live stations and traditional menus for team events, celebrations and business gatherings.', 'alt_text' => 'Vada, bonda and banana chips on a banana leaf for a corporate gathering'],
      4 => ['description' => 'Indian sweets, snacks and traditional feasts, freshly prepared for Diwali and special celebrations.', 'alt_text' => 'Laddus, murukku and festive sweets in brass bowls for Diwali and special occasions'],
    ],
    'features' => [
      1 => ['description' => 'Skilled chefs preparing authentic South Indian dishes from traditional recipes.'],
      2 => ['description' => 'Pure ingredients, better taste. Every dish is prepared fresh for your event.'],
      3 => ['description' => 'Menus tailored to your needs, your guests and your occasion, including vegetarian options.'],
      4 => ['description' => 'Professional, attentive service from enquiry to final serving.'],
      5 => ['description' => 'For your special moments, from weddings to birthday parties.'],
      6 => ['description' => 'A trusted local caterer for all your catering needs in Fontana, CA.'],
    ],
  ],
  'faqs' => [
    ['What types of events do you cater?', 'BB Caterers provides Indian catering for weddings and receptions, birthday and private parties, corporate gatherings, and festivals and special occasions in Fontana, CA and nearby areas.'],
    ['Do you offer Indian wedding catering?', 'Yes. Our Indian wedding catering includes traditional South Indian menus, live food stations and festive sweets, planned around your ceremony, reception and guest list.'],
    ['What is a live dosa station, and can I add one to my event?', 'A live dosa station is a counter where our team prepares dosas fresh and serves them hot with chutneys and sambar. It is a popular choice for weddings, parties and corporate events.'],
    ['Do you offer vegetarian catering?', 'Yes. Our South Indian menus include a variety of vegetarian dishes, and we can create a fully vegetarian menu based on your event and guest preferences.'],
    ['How do I get a catering quote?', 'Fill in the enquiry form or contact BB Caterers with your event date, event type, number of guests and menu preferences. We recommend contacting us early for weddings and festival seasons.'],
  ],
];
