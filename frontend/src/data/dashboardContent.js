/** Shared dashboard content inspired by Taskora financial model screen */

export const serviceCategories = [
  { n: 1, title: 'Home Services', hint: 'Cleaning, Plumbing, Electrical, AC' },
  { n: 2, title: 'Construction & Renovation', hint: 'Civil, renovation, finishing' },
  { n: 3, title: 'Architects & Interior Design', hint: 'Design & space planning' },
  { n: 4, title: 'Legal Services', hint: 'Lawyers & documentation' },
  { n: 5, title: 'Chartered Accountants & Taxation', hint: 'Audit, tax, compliance' },
  { n: 6, title: 'IT Services & Software', hint: 'Dev, support, cloud' },
  { n: 7, title: 'Tutors & Education', hint: 'Home & online tutoring' },
  { n: 8, title: 'Corporate & Business Services', hint: 'Ops & admin support' },
  { n: 9, title: 'Facility Management', hint: 'Buildings & campuses' },
  { n: 10, title: 'HR & Recruitment', hint: 'Hiring & payroll' },
  { n: 11, title: 'Digital Marketing & Media', hint: 'Ads, content, brand' },
  { n: 12, title: 'Health & Wellness', hint: 'Care & fitness partners' },
  { n: 13, title: 'Event Management', hint: 'Weddings & corporate' },
  { n: 14, title: 'Logistics & Transport', hint: 'Delivery & movers' },
  { n: 15, title: 'Security Services', hint: 'Guards & CCTV' },
  { n: 16, title: 'Insurance & Financial', hint: 'Partner-based referrals' },
]

export const commissionTiers = [
  { value: 'Up to 2,000', rate: '15%' },
  { value: '2,001 – 10,000', rate: '13%' },
  { value: '10,001 – 50,000', rate: '12%' },
  { value: '50,001 – 200,000', rate: '10%' },
  { value: '200,000+', rate: '8–10%' },
]

export const categoryRevenueModel = [
  { category: 'Home Services', model: 'Per Service Commission', rate: '12% – 15%', extra: 'Recurring bookings, AMC' },
  { category: 'Construction & Renovation', model: 'Project Commission', rate: '8% – 12%', extra: 'Milestone billing' },
  { category: 'Legal / CA / Tax', model: 'Per Engagement', rate: '10% – 14%', extra: 'Retainer plans' },
  { category: 'IT & Software', model: 'Project / Retainer', rate: '10% – 15%', extra: 'Support contracts' },
  { category: 'Corporate & Facility', model: 'Contract Commission', rate: '8% – 12%', extra: 'SLA packages' },
  { category: 'Insurance & Financial', model: 'Partner Referral', rate: 'Partner-based', extra: 'Introductions only' },
]

export const platformHighlights = [
  { label: 'GMV potential', value: 'PKR 35B+' },
  { label: 'Gross margin', value: '60%' },
  { label: 'Net margin target', value: '20%+' },
  { label: 'Break-even', value: '18–24 mo' },
]

export const platformStats = [
  { value: '5M+', label: 'Happy Users' },
  { value: '500K+', label: 'Verified Pros' },
  { value: '100+', label: 'Cities' },
  { value: '15+', label: 'Categories' },
  { value: '1', label: 'Super App' },
]

export const revenueStreams = [
  { label: 'Service Commission', pct: 45 },
  { label: 'Subscriptions', pct: 15 },
  { label: 'Value-Added', pct: 10 },
  { label: 'Featured Listings', pct: 10 },
  { label: 'Advertising', pct: 8 },
  { label: 'Other', pct: 12 },
]

export const growthBars = [
  { year: 'Y1', value: 22 },
  { year: 'Y2', value: 38 },
  { year: 'Y3', value: 55 },
  { year: 'Y4', value: 72 },
  { year: 'Y5', value: 100 },
]

export const feasibilityRows = [
  { year: 'Year 1', gmv: '4.0B', orders: '1.2M', take: '12%', revenue: '600M', gp: '360M', npm: '8%', np: '48M' },
  { year: 'Year 2', gmv: '9.5B', orders: '2.8M', take: '12%', revenue: '1.4B', gp: '840M', npm: '12%', np: '168M' },
  { year: 'Year 3', gmv: '18B', orders: '5.1M', take: '13%', revenue: '2.8B', gp: '1.7B', npm: '16%', np: '450M' },
  { year: 'Year 4', gmv: '26B', orders: '7.4M', take: '13%', revenue: '3.7B', gp: '2.2B', npm: '18%', np: '670M' },
  { year: 'Year 5', gmv: '35B', orders: '10M+', take: '13%', revenue: '4.55B', gp: '2.7B', npm: '20%+', np: '910M' },
]

export const commissionFlow = [
  'Customer Pays',
  'Provider Earns',
  'Taskora Commission',
  'Payout to Provider',
]

export const enablers = [
  'AI Matching',
  'Secure Payments',
  'Verification & Trust',
  'Analytics Dashboard',
  '24/7 Support',
]

export const businessFlow = [
  'Demand',
  'Verified Pros',
  'Taskora',
  'Quality Delivery',
  'Trust & Reviews',
  'Repeat Business',
]
