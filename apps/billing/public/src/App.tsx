import { Check, Phone, Mail, Laptop, Settings, Database } from 'lucide-react';
import './App.css';

function App() {
  const services = [
    'Professional Websites & E-Commerce',
    'Automated Dashboards & Reporting',
    'ERP & Custom Systems',
    'Loan Management Systems',
    'Inventory & POS Systems',
    'Mobile Apps',
    'AI & Automation Solutions',
  ];

  const packages = [
    {
      icon: Laptop,
      name: 'Starter Websites',
      price: '3,500',
      originalPrice: 'R 5,500',
    },
    {
      icon: Settings,
      name: 'Business Automation',
      price: '12,000',
      originalPrice: 'R 15,900',
    },
    {
      icon: Database,
      name: 'ERP & Custom Systems',
      price: '35,000',
      originalPrice: null,
    },
  ];

  return (
    <div className="min-h-screen relative overflow-hidden">
      {/* Background Image */}
      <div 
        className="fixed inset-0 bg-cover bg-center bg-no-repeat"
        style={{ backgroundImage: 'url(/assets/city-bg.jpg)' }}
      />
      
      {/* Dark Overlay */}
      <div className="fixed inset-0 bg-gradient-to-b from-slate-900/70 via-slate-900/80 to-slate-950/95" />
      
      {/* Content */}
      <div className="relative z-10">
        {/* Hero Section */}
        <section className="min-h-screen flex flex-col">
          {/* Header/Logo */}
          <div className="pt-8 pb-4 px-4 sm:px-6 lg:px-8">
            <div className="max-w-7xl mx-auto">
              <div className="flex items-center justify-center gap-3">
                {/* Logo */}
                <div className="relative">
                  <svg width="60" height="60" viewBox="0 0 60 60" fill="none" className="animate-pulse">
                    <circle cx="30" cy="30" r="28" stroke="#eab308" strokeWidth="2" fill="none"/>
                    <path d="M20 20L40 40M40 20L20 40" stroke="#eab308" strokeWidth="3" strokeLinecap="round"/>
                    <circle cx="30" cy="30" r="8" stroke="#eab308" strokeWidth="2" fill="none"/>
                  </svg>
                </div>
                <h1 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight">
                  <span className="text-white">Xquisite</span>{' '}
                  <span className="text-gold text-shadow-gold">Creations</span>
                </h1>
              </div>
              <p className="text-center text-gold-light text-sm sm:text-base mt-2 font-medium tracking-wide">
                Empowering Your Business Through Technology & Innovation
              </p>
            </div>
          </div>

          {/* Main Content Grid */}
          <div className="flex-1 px-4 sm:px-6 lg:px-8 pb-8">
            <div className="max-w-7xl mx-auto">
              <div className="grid lg:grid-cols-2 gap-8 items-start">
                {/* Left Column */}
                <div className="space-y-6">
                  {/* Service Icons */}
                  <div className="flex flex-wrap justify-center lg:justify-start gap-6 sm:gap-10">
                    <div className="text-center">
                      <div className="w-24 h-20 sm:w-32 sm:h-24 relative">
                        <img 
                          src="/assets/asset_1.png" 
                          alt="Smart Websites" 
                          className="w-full h-full object-contain"
                        />
                      </div>
                      <p className="text-white text-xs sm:text-sm font-medium mt-1">Smart Websites</p>
                    </div>
                    <div className="text-center">
                      <div className="w-32 h-20 sm:w-44 sm:h-24 relative">
                        <img 
                          src="/assets/asset_2.png" 
                          alt="Business Automation" 
                          className="w-full h-full object-contain"
                        />
                      </div>
                      <p className="text-white text-xs sm:text-sm font-medium mt-1">Business Automation</p>
                    </div>
                  </div>

                  {/* Name & Title */}
                  <div className="text-center lg:text-left">
                    <h2 className="text-3xl sm:text-4xl lg:text-5xl font-bold text-white">
                      XOLISWA <span className="text-gold text-shadow-gold">MASUKU</span>
                    </h2>
                    <p className="text-white/80 text-lg sm:text-xl font-semibold mt-1">I BUILD:</p>
                  </div>

                  {/* Services List */}
                  <div className="space-y-2">
                    {services.map((service, index) => (
                      <div 
                        key={index} 
                        className="flex items-center gap-3 group"
                      >
                        <div className="flex-shrink-0 w-5 h-5 rounded-full bg-gold/20 flex items-center justify-center group-hover:bg-gold/40 transition-colors">
                          <Check className="w-3 h-3 text-gold" />
                        </div>
                        <span className="text-white text-sm sm:text-base font-medium group-hover:text-gold-light transition-colors">
                          {service}
                        </span>
                      </div>
                    ))}
                  </div>

                  {/* Key Packages */}
                  <div className="pt-4">
                    <h3 className="text-xl sm:text-2xl font-bold text-gold text-center lg:text-left mb-4 text-shadow-gold">
                      Key Packages
                    </h3>
                    <div className="space-y-3">
                      {packages.map((pkg, index) => (
                        <div 
                          key={index}
                          className="glass-card rounded-lg p-3 sm:p-4 flex items-center gap-4 hover:border-gold/60 transition-all duration-300 hover:shadow-gold group"
                        >
                          <div className="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 rounded-lg bg-gold/20 flex items-center justify-center group-hover:bg-gold/30 transition-colors">
                            <pkg.icon className="w-5 h-5 sm:w-6 sm:h-6 text-gold" />
                          </div>
                          <div className="flex-1">
                            <p className="text-white font-semibold text-sm sm:text-base">{pkg.name}</p>
                            {pkg.originalPrice && (
                              <p className="text-white/50 text-xs line-through">FROM {pkg.originalPrice}</p>
                            )}
                          </div>
                          <div className="text-right">
                            <p className="text-white/60 text-xs">FROM</p>
                            <p className="text-gold font-bold text-lg sm:text-xl">{pkg.price}</p>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>

                {/* Right Column - Portrait */}
                <div className="hidden lg:flex justify-center items-end h-full">
                  <div className="relative">
                    <img 
                      src="/assets/portrait.png" 
                      alt="Xoliswa Masuku" 
                      className="w-full max-w-md xl:max-w-lg object-contain"
                    />
                    {/* Glow Effect */}
                    <div className="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* CTA Section */}
          <div className="px-4 sm:px-6 lg:px-8 pb-8">
            <div className="max-w-7xl mx-auto">
              <div className="bg-gold-gradient rounded-lg p-4 sm:p-6 text-center shadow-gold-lg">
                <h3 className="text-slate-900 font-bold text-xl sm:text-2xl lg:text-3xl">
                  Get a Free Consultation
                </h3>
              </div>
              
              {/* Contact Info */}
              <div className="flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-8 mt-4">
                <a 
                  href="tel:0674017419" 
                  className="flex items-center gap-2 text-white hover:text-gold transition-colors group"
                >
                  <div className="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center group-hover:bg-gold/30 transition-colors">
                    <Phone className="w-5 h-5 text-gold" />
                  </div>
                  <span className="font-semibold text-lg">067 401 7419</span>
                </a>
                <a 
                  href="mailto:bester12@outlook.com" 
                  className="flex items-center gap-2 text-white hover:text-gold transition-colors group"
                >
                  <div className="w-10 h-10 rounded-full bg-gold/20 flex items-center justify-center group-hover:bg-gold/30 transition-colors">
                    <Mail className="w-5 h-5 text-gold" />
                  </div>
                  <span className="font-semibold text-lg">bester12@outlook.com</span>
                </a>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  );
}

export default App;
