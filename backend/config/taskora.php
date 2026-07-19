<?php

return [
    'meta' => [
        'title' => 'Taskora — One App. Every Service. Every Need.',
        'tagline' => 'Powered by Trovec Technologies',
    ],

    'promo' => [
        'text' => '🚀 We Are Launching — Life Happens, Taskora Helps · Up To 20% Off Your First Booking',
    ],

    'nav' => [
        'links' => [
            ['label' => 'Services', 'href' => '#services'],
            ['label' => 'Knowledge', 'href' => '#knowledge'],
            ['label' => 'For Business', 'href' => '#business'],
            ['label' => 'For Professionals', 'href' => '#professional'],
            ['label' => 'Company', 'href' => '#company'],
        ],
        'cta' => ['label' => 'Download App', 'href' => '#download'],
    ],

    'hero' => [
        'tag' => "Pakistan's Trusted Services Ecosystem",
        'title' => ['One app.', 'Every service,', 'every need.'],
        'title_emphasis' => 'every need.',
        'lede' => 'Taskora connects you with 10,000+ verified professionals across 100+ service categories — home, business, and property — booked in minutes, tracked in real time.',
        'ctas' => [
            ['label' => 'Download the App', 'href' => '#download', 'variant' => 'gold'],
            ['label' => 'Explore Services', 'href' => '#services', 'variant' => 'ghost'],
        ],
        'stats' => [
            ['value' => '100+', 'label' => 'Services'],
            ['value' => '10,000+', 'label' => 'Verified Pros'],
            ['value' => '50,000+', 'label' => 'Happy Customers'],
            ['value' => '24/7', 'label' => 'Support'],
        ],
        'phone' => [
            'categories' => ['Home', 'Business', 'Facility', 'Construction'],
            'popular_services' => [
                ['name' => 'Deep Cleaning', 'rating' => '4.9'],
                ['name' => 'Electrician Visit', 'rating' => '4.8'],
                ['name' => 'AC Service', 'rating' => '4.7'],
            ],
        ],
        'ticker' => [
            'Electricians', 'Plumbers', 'Interior Design', 'Facility Management',
            'Movers & Packers', 'Pest Control', 'Architecture', 'Housing Societies',
            'AC Repair', 'Construction',
        ],
    ],

    'stats_bar' => [
        ['value' => '100+', 'label' => 'Service Categories'],
        ['value' => '10,000+', 'label' => 'Verified Professionals'],
        ['value' => '50,000+', 'label' => 'Happy Customers'],
        ['value' => '100%', 'label' => 'Satisfaction Guarantee'],
    ],

    'company' => [
        'tag' => 'Who We Are',
        'title' => "Built for Pakistan's\nservice economy.",
        'description' => 'Taskora is a digital platform connecting customers with verified professionals for home services, business support, facility management, architecture, and construction — all from one place.',
        'why_items' => [
            'Verified professionals, screened before they reach your door.',
            'Transparent pricing with no hidden charges.',
            'Real-time tracking from booking to job completion.',
            'Secure digital payments, every transaction protected.',
            'From single homes to full enterprise facility contracts.',
        ],
    ],

    'flow' => [
        'tag' => 'What Makes Taskora Different',
        'title' => "Not just booking.\nThe whole journey.",
        'description' => 'Most platforms stop at booking. Taskora takes customers from first question to finished job — and keeps them covered after.',
        'steps' => ['Learn', 'Compare', 'Estimate', 'Plan', 'Book', 'Track', 'Pay', 'Review', 'Maintain'],
    ],

    'services' => [
        'tag' => 'Service Directory',
        'title' => 'Everything, catalogued.',
        'description' => 'A structured catalogue of professionals across home, business, and property — each category verified through the same trusted system.',
        'categories' => [
            ['code' => 'T01 — HOME SERVICES', 'title' => 'Home Services', 'items' => ['House & Deep Cleaning', 'Kitchen & Bathroom Cleaning', 'Sofa, Carpet & Mattress Cleaning', 'Water Tank & Glass Cleaning', 'Laundry, Pest Control, Disinfection']],
            ['code' => 'T02 — HOME MAINTENANCE', 'title' => 'Home Maintenance', 'items' => ['Electricians & Plumbers', 'Carpenters & Painters', 'AC & Appliance Repair', 'CCTV & Solar Installation', 'Smart Home Automation']],
            ['code' => 'T03 — ARCHITECTURE & DESIGN', 'title' => 'Architecture & Design', 'items' => ['Architectural & Interior Design', 'Renovation & Landscape Design', 'BOQ & Quantity Surveying', 'Structural & MEP Design', '3D Rendering & BIM Services']],
            ['code' => 'T04 — CONSTRUCTION', 'title' => 'Construction Services', 'items' => ['Grey Structure & Finishing', 'Waterproofing & Flooring', 'False Ceilings', 'Aluminum & Glass Work', 'Steel Fabrication']],
            ['code' => 'T05 — BUSINESS & FACILITY', 'title' => 'Business & Facility Management', 'items' => ['Office Cleaning & Janitorial', 'Security & Reception Staff', 'HVAC & Preventive Maintenance', 'Annual Maintenance Contracts', 'Waste Management']],
            ['code' => 'T06 — HOUSING SOCIETIES', 'title' => 'Housing Society Solutions', 'items' => ['Resident Service Portal', 'Complaint & Vendor Management', 'Visitor Management', 'Community Announcements', 'Emergency Support']],
            ['code' => 'T07 — COMMERCIAL', 'title' => 'Commercial Solutions', 'items' => ['Malls, Hotels & Restaurants', 'Hospitals & Schools', 'Warehouses & Factories', 'Banks & Government Offices']],
            ['code' => 'T08 — MOVING & LOGISTICS', 'title' => 'Moving & Logistics', 'items' => ['Packers & Movers', 'Office Relocation', 'Furniture Assembly', 'Storage Solutions']],
            ['code' => 'T09 — PERSONAL SERVICES', 'title' => 'Personal Services', 'items' => ['Beauticians & Tutors', 'Drivers & Cooks', 'Babysitters & Caregivers', 'Pet Care & Fitness Trainers']],
        ],
    ],

    'knowledge' => [
        'tag' => 'Beyond the Marketplace',
        'title' => "Pakistan's largest\nhome & property\nknowledge base.",
        'description' => 'Guides, checklists, and estimators that build trust before a customer ever books — so every decision is informed.',
        'guides' => [
            'Home maintenance guides',
            'Construction planning resources',
            'Architecture & interior inspiration',
            'Renovation checklists',
            'Cost estimation articles',
            'Facility management best practices',
            'Energy-saving & smart home tips',
            'Seasonal maintenance calendars',
        ],
    ],

    'calculator' => [
        'tag' => 'Smart Digital Tools',
        'title' => 'Try the cost estimator.',
        'description' => 'Get an instant, illustrative price range before you book — final pricing is always confirmed by your professional.',
        'note' => 'Illustrative estimate based on typical Taskora bookings. Your final price is confirmed by the assigned professional before work begins — no hidden charges.',
        'service_types' => [
            ['id' => 'house_cleaning', 'label' => 'House Cleaning', 'base_price' => 450],
            ['id' => 'deep_cleaning', 'label' => 'Deep Cleaning', 'base_price' => 900],
            ['id' => 'electrician', 'label' => 'Electrician Visit & Repair', 'base_price' => 1200],
            ['id' => 'plumbing', 'label' => 'Plumbing Repair', 'base_price' => 1500],
            ['id' => 'ac_service', 'label' => 'AC Service & Repair', 'base_price' => 2200],
            ['id' => 'painting', 'label' => 'Painting (per room)', 'base_price' => 3500],
            ['id' => 'movers', 'label' => 'Packers & Movers', 'base_price' => 6000],
        ],
        'frequencies' => [
            ['id' => 'one_time', 'label' => 'One-Time', 'factor' => 1],
            ['id' => 'weekly', 'label' => 'Weekly Plan (10% off)', 'factor' => 0.9],
            ['id' => 'monthly', 'label' => 'Monthly Plan (15% off)', 'factor' => 0.85],
        ],
        'size' => ['min' => 3, 'max' => 20, 'default' => 5, 'unit' => 'Marla'],
    ],

    'tools' => [
        'tag' => 'More Planning Tools',
        'title' => 'Plan before you book.',
        'items' => [
            'Construction budget calculator',
            'Paint & tile quantity calculator',
            'Solar savings calculator',
            'AMC savings calculator',
            'Home maintenance planner',
            'Property inspection checklist',
            'Project timeline planner',
            'Moving cost estimator',
        ],
    ],

    'dashboard' => [
        'tag' => 'Your Account',
        'title' => "A dashboard for\nevery customer.",
        'description' => 'Track bookings, manage payments, save favorite professionals, and rate every job — all from one account.',
        'user' => ['name' => 'Ayesha Khan', 'email' => 'ayesha@customer.taskora.com'],
        'nav' => ['Bookings', 'Payments & Invoices', 'Saved Professionals', 'My Reviews', 'Account Settings'],
        'cards' => [
            ['value' => '12', 'label' => 'Total Bookings'],
            ['value' => 'PKR 38,400', 'label' => 'Total Spent'],
            ['value' => '4.9★', 'label' => 'Avg. Given Rating'],
        ],
        'bookings' => [
            ['who' => 'Bilal Ahmed — Electrician', 'what' => 'Circuit repair · Today, 4:30 PM · DHA Phase 5', 'status' => 'En Route'],
            ['who' => 'CleanPro Team — Deep Cleaning', 'what' => '3-Bed Apartment · Tomorrow, 10:00 AM', 'status' => 'Confirmed'],
        ],
        'rate_prompt' => [
            'title' => 'Rate your last service',
            'subtitle' => 'AC Service with Imran M. — completed Tuesday',
        ],
    ],

    'reviews' => [
        'tag' => 'Ratings & Reviews',
        'title' => "Trusted by thousands\nof real bookings.",
        'score' => '4.8',
        'count' => 'From 28,600+ Reviews',
        'distribution' => [
            ['stars' => 5, 'percent' => 78],
            ['stars' => 4, 'percent' => 15],
            ['stars' => 3, 'percent' => 4],
            ['stars' => 2, 'percent' => 2],
            ['stars' => 1, 'percent' => 1],
        ],
        'testimonials' => [
            ['stars' => 5, 'text' => 'Booked an electrician at 9pm for a tripped circuit — someone was at the door in under an hour.', 'author' => 'Sample Review — DHA, Lahore'],
            ['stars' => 5, 'text' => "We moved our office's entire facility management to one dashboard. Fewer vendors, fewer headaches.", 'author' => 'Sample Review — Office Manager, Karachi'],
            ['stars' => 4, 'text' => 'Our housing society uses it for maintenance requests now — residents just log in and track status.', 'author' => 'Sample Review — Society Committee, Islamabad'],
        ],
        'note' => 'Illustrative reviews for demonstration purposes.',
    ],

    'career' => [
        'tag' => 'Professional Network',
        'title' => "From registration\nto regional manager.",
        'description' => 'Every professional moves through the same verified pipeline, then grows through a transparent career path.',
        'steps' => [
            ['title' => 'Registration', 'description' => 'Digital sign-up in minutes.'],
            ['title' => 'Verification', 'description' => 'Identity & document review.'],
            ['title' => 'Training', 'description' => 'Skills assessment & onboarding.'],
            ['title' => 'Activation', 'description' => 'Profile goes live on Taskora.'],
            ['title' => 'Job Assignment', 'description' => 'Matched to nearby bookings.'],
            ['title' => 'Performance', 'description' => 'Rated on every completed job.'],
            ['title' => 'Growth', 'description' => 'Unlocks the career path.'],
        ],
        'levels' => ['Bronze', 'Silver', 'Gold', 'Platinum', 'Master Pro', 'Team Leader', 'Regional Manager'],
    ],

    'solutions' => [
        'tag' => 'Beyond the Household',
        'title' => "Solutions for\nevery scale.",
        'description' => 'One partner, one dashboard, complete operational support — whether you manage a home, a society, or a portfolio of sites.',
        'items' => [
            ['tag' => 'For Business', 'title' => 'Business Solutions', 'description' => 'A centralized platform to manage facility operations, maintenance requests, vendors, and recurring services.', 'features' => ['Corporate office facility management', 'Recurring maintenance contracts', 'Vendor consolidation', 'Digital reporting dashboard']],
            ['tag' => 'For Communities', 'title' => 'Housing Society Solutions', 'description' => 'Digital tools built for modern residential communities and their management committees.', 'features' => ['Resident service portal', 'Complaint & visitor management', 'Community announcements', 'Society-wide dashboard']],
            ['tag' => 'For Enterprise', 'title' => 'Enterprise Solutions', 'description' => 'Multi-site service coverage for hospitality, healthcare, education, and industrial facilities.', 'features' => ['Multi-location coordination', 'Compliance-ready reporting', 'Dedicated account management', 'Custom SLAs']],
        ],
    ],

    'marketplace' => [
        'tag' => 'Community Marketplace',
        'title' => "Where customers &\nprofessionals connect.",
        'description' => 'A living record of real work — reviews, portfolios, and galleries that let quality speak for itself.',
        'tags' => ['Verified Reviews', 'Professional Portfolios', 'Before & After Galleries', 'Q&A Forums', 'Expert Recommendations', 'Success Stories', 'Seasonal Campaigns', 'Local Recommendations'],
    ],

    'trust' => [
        'tag' => 'Trust & Safety',
        'title' => 'Every professional is verified before dispatch.',
        'description' => 'Onboarding may include identity verification, document review, skills assessment, and ongoing customer quality monitoring. Exact steps vary by service category and applicable legal requirements.',
        'badges' => ['SECP Registered', 'Data Privacy Compliant', 'Secure Cloud Hosting', 'Audit Logged'],
        'checks' => ['Identity Verification', 'Document Review', 'Skills Assessment', 'Customer Quality Monitoring', 'Secure In-App Payments'],
    ],

    'customers' => [
        'tag' => 'Who We Serve',
        'title' => "Built for every kind\nof customer.",
        'description' => 'From a single apartment to a portfolio of industrial sites — one platform scales with you.',
        'tags' => ['Homeowners', 'Apartment Residents', 'Overseas Pakistanis', 'Property Investors', 'Restaurants & Hotels', 'Hospitals & Schools', 'Banks', 'Developers', 'Housing Societies', 'Government Organizations', 'Industrial Facilities'],
    ],

    'business_model' => [
        'tag' => 'Business Model',
        'title' => "How Taskora\nsustains the platform.",
        'description' => 'Diversified, transparent revenue streams — built to keep the platform reliable for customers and fair for professionals.',
        'streams_count' => 10,
        'streams' => [
            ['label' => 'Marketplace Commissions', 'percent' => 40, 'color' => '#0E8F57'],
            ['label' => 'Corporate Contracts', 'percent' => 20, 'color' => '#065F46'],
            ['label' => 'Housing Society Contracts', 'percent' => 10, 'color' => '#D4AF37'],
            ['label' => 'Subscription Plans', 'percent' => 10, 'color' => '#7FB8A0'],
            ['label' => 'Facility Management', 'percent' => 8, 'color' => '#0B0D0C'],
            ['label' => 'Featured Listings', 'percent' => 4, 'color' => '#C9A96A'],
            ['label' => 'Advertising', 'percent' => 3, 'color' => '#9AA79E'],
            ['label' => 'Training & Certification', 'percent' => 2, 'color' => '#4E6B5F'],
            ['label' => 'Franchise Fees', 'percent' => 2, 'color' => '#D9D2BE'],
            ['label' => 'SaaS & Analytics (Future)', 'percent' => 1, 'color' => '#E6E6E6'],
        ],
        'note' => 'Illustrative planning split based on growth assumptions — not a financial guarantee.',
    ],

    'professional' => [
        'tag' => 'Join The Network',
        'title' => 'Become a Taskora professional.',
        'description' => "Join Pakistan's fastest-growing professional network and get consistent work, weekly payouts, and a digital reputation that follows you.",
        'cta' => ['label' => 'Apply to Join', 'href' => '#download'],
        'benefits' => ['More customers', 'Weekly payouts', 'Digital profile', 'Performance rewards', 'Training & certification', 'Flexible hours'],
    ],

    'vision' => [
        'tag' => 'Long-Term Vision',
        'title' => 'Building the digital infrastructure for Pakistan\'s service economy — not just another booking app, but the trusted platform where people discover services, gain knowledge, hire professionals, and operate businesses, all in one connected ecosystem.',
        'subtitle' => 'Taskora is powered by Trovec Technologies.',
        'legal' => 'Taskora (Pvt.) Ltd. · Registered under SECP · A Trovec Technologies Company',
    ],

    'cta' => [
        'tag' => 'Scan · Book · Relax',
        'title' => "One app.\nEvery service. Every need.",
        'buttons' => [
            ['label' => 'Download for iOS', 'href' => '#', 'variant' => 'fill'],
            ['label' => 'Download for Android', 'href' => '#', 'variant' => 'fill'],
            ['label' => 'Talk to Sales', 'href' => '#', 'variant' => 'ghost'],
        ],
    ],

    'footer' => [
        'description' => "Pakistan's digital platform for trusted, verified home, business, and property services — all from one app.",
        'columns' => [
            ['title' => 'Services', 'links' => [
                ['label' => 'Home Services', 'href' => '#services'],
                ['label' => 'Home Maintenance', 'href' => '#services'],
                ['label' => 'Architecture & Design', 'href' => '#services'],
                ['label' => 'Construction', 'href' => '#services'],
            ]],
            ['title' => 'Solutions', 'links' => [
                ['label' => 'Business', 'href' => '#business'],
                ['label' => 'Housing Societies', 'href' => '#business'],
                ['label' => 'Enterprise', 'href' => '#business'],
            ]],
            ['title' => 'Company', 'links' => [
                ['label' => 'About Us', 'href' => '#company'],
                ['label' => 'Become a Professional', 'href' => '#professional'],
                ['label' => 'Trust & Safety', 'href' => '#'],
            ]],
            ['title' => 'Get The App', 'links' => [
                ['label' => 'iOS', 'href' => '#download'],
                ['label' => 'Android', 'href' => '#download'],
            ]],
        ],
        'copyright' => '© 2026 Taskora (Pvt.) Ltd. · A Trovec Technologies Company',
        'legal_links' => 'Privacy · Terms · Sitemap',
    ],
];
