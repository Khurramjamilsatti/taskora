/** Bookable services used by the customer dashboard */

export const bookableCategories = [
  {
    title: 'Home Services',
    services: [
      'House & Deep Cleaning',
      'Plumbing & Electrical',
      'AC Repair & Installation',
      'Carpentry & Painting',
      'Pest Control & Disinfection',
    ],
  },
  {
    title: 'Construction & Renovation',
    services: [
      'Grey Structure & Finishing',
      'Renovation & Remodeling',
      'Waterproofing & Flooring',
      'Project Management',
      'Quantity Surveying & BOQ',
    ],
  },
  {
    title: 'Architects & Interior Design',
    services: [
      'Architectural Design',
      'Interior & Landscape Design',
      '3D Rendering & BIM',
      'Structural & MEP Design',
      'Renovation Consultancy',
    ],
  },
  {
    title: 'Legal Services',
    services: [
      'Corporate & Contract Law',
      'Property & Family Law',
      'Legal Documentation',
      'Dispute Resolution',
      'Company Registration',
    ],
  },
  {
    title: 'Chartered Accountants & Taxation',
    services: [
      'Tax Filing & Advisory',
      'Audit & Assurance',
      'SECP Company Registration',
      'Bookkeeping & Payroll',
      'Financial Consulting',
    ],
  },
  {
    title: 'IT Services & Software',
    services: [
      'Web & App Development',
      'Software & AI Solutions',
      'Cloud & Cybersecurity',
      'IT Support & Networking',
      'Data Entry & Analytics',
    ],
  },
  {
    title: 'Tutors & Education',
    services: [
      'Home & Online Tutors',
      'Test Preparation',
      'Language Coaching',
      'Skill Development',
      'Computer Training',
    ],
  },
  {
    title: 'Corporate & Business Services',
    services: [
      'Business Setup & Consulting',
      'Virtual Assistance',
      'Corporate Contracts',
      'Market Research',
      'Strategy Advisory',
    ],
  },
  {
    title: 'Facility Management',
    services: [
      'Building Maintenance',
      'Janitorial Services',
      'Waste Management',
      'HVAC & AMC Contracts',
      'Vendor Management',
    ],
  },
  {
    title: 'HR & Recruitment',
    services: [
      'Recruitment & Staffing',
      'Payroll Management',
      'HR Consulting',
      'Background Checks',
      'Training & Certification',
    ],
  },
  {
    title: 'Digital Marketing & Media',
    services: [
      'SEO & Social Media',
      'Graphic Design',
      'Photography & Videography',
      'Branding & Content',
      'Paid Advertising',
    ],
  },
  {
    title: 'Health & Wellness',
    services: [
      'Home Nursing & Caregiving',
      'Fitness Trainers',
      'Beauty & Salon',
      'Wellness Consulting',
      'Elderly Care',
    ],
  },
  {
    title: 'Event Management',
    services: [
      'Event Planning & Catering',
      'Photography & Décor',
      'Venue & Vendor Coordination',
      'Corporate Events',
      'Ticketing & Sponsorship',
    ],
  },
  {
    title: 'Logistics & Transport',
    services: [
      'Packers & Movers',
      'Courier & Delivery',
      'Vehicle Rentals',
      'Warehousing',
      'Intercity Transport',
    ],
  },
  {
    title: 'Security Services',
    services: [
      'CCTV & Alarm Systems',
      'Security Guards',
      'Access Control',
      'Fire Safety',
      'Background Verification',
    ],
  },
  {
    title: 'Insurance, Financial & Investment',
    services: [
      'Insurance Marketplace',
      'SME Financing',
      'Investment Referral',
      'Savings & Gold Plans',
      'Invoice Financing',
    ],
  },
]

export function flattenServices() {
  return bookableCategories.flatMap((cat) =>
    cat.services.map((service) => ({
      category: cat.title,
      service,
    })),
  )
}
