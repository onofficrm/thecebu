import React from 'react';
import { ServiceMeta } from '../../types';
import { ServiceIcon } from './ServiceIcon';

interface ServiceCardProps {
  service: ServiceMeta;
  onClick: (serviceId: ServiceMeta['id']) => void;
  layout?: 'grid' | 'list' | 'compact';
}

export const ServiceCard: React.FC<ServiceCardProps> = ({
  service,
  onClick,
  layout = 'grid',
}) => {
  if (layout === 'compact') {
    return (
      <button
        type="button"
        onClick={() => onClick(service.id)}
        className="flex flex-col items-center justify-center p-2.5 bg-white border border-[#E1ECF3] rounded-xl hover:border-[#079BE8] hover:shadow-xs transition-all text-center group min-h-[76px] w-full"
      >
        <div
          className="w-10 h-10 rounded-xl flex items-center justify-center mb-1.5 transition-transform group-hover:scale-105"
          style={{ backgroundColor: `${service.color}15`, color: service.color }}
        >
          <ServiceIcon iconName={service.icon} size={20} color={service.color} />
        </div>
        <span className="text-xs font-bold text-[#183247] tracking-tight truncate w-full">
          {service.name}
        </span>
      </button>
    );
  }

  if (layout === 'list') {
    return (
      <button
        type="button"
        onClick={() => onClick(service.id)}
        className="flex items-center justify-between p-3.5 bg-white border border-[#E1ECF3] rounded-2xl hover:border-[#079BE8] hover:shadow-2xs transition-all w-full text-left group min-h-[58px]"
      >
        <div className="flex items-center gap-3 min-w-0">
          <div
            className="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
            style={{ backgroundColor: `${service.color}15`, color: service.color }}
          >
            <ServiceIcon iconName={service.icon} size={20} color={service.color} />
          </div>
          <div className="min-w-0">
            <div className="flex items-center gap-2">
              <span className="text-sm font-bold text-[#183247] tracking-tight">
                {service.name}
              </span>
              {service.badge && (
                <span className="text-[10px] font-bold text-[#075A9D] bg-[#EAF8FF] px-1.5 py-0.5 rounded border border-[#B9E5FC]">
                  {service.badge}
                </span>
              )}
            </div>
            <p className="text-xs text-[#617789] truncate mt-0.5">
              {service.description}
            </p>
          </div>
        </div>
      </button>
    );
  }

  // Default Grid layout
  return (
    <button
      type="button"
      onClick={() => onClick(service.id)}
      className="relative flex flex-col items-center justify-center p-3 bg-white border border-[#E1ECF3] rounded-2xl hover:border-[#079BE8] hover:shadow-xs transition-all text-center group min-h-[88px] w-full"
    >
      {service.badge && (
        <span className="absolute top-2 right-2 text-[9px] font-extrabold text-[#075A9D] bg-[#EAF8FF] px-1 py-0.2 rounded leading-tight border border-[#B9E5FC]">
          {service.badge}
        </span>
      )}
      <div
        className="w-11 h-11 rounded-2xl flex items-center justify-center mb-1.5 transition-transform group-hover:scale-108"
        style={{ backgroundColor: `${service.color}15`, color: service.color }}
      >
        <ServiceIcon iconName={service.icon} size={22} color={service.color} />
      </div>
      <span className="text-xs font-bold text-[#183247] tracking-tight leading-none mt-1">
        {service.name}
      </span>
      <span className="text-[10px] font-medium text-[#8799A8] mt-1 tracking-tight leading-none">
        {service.enName}
      </span>
    </button>
  );
};
