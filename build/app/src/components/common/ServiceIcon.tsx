import React from 'react';
import {
  Newspaper,
  BookOpen,
  ShoppingBag,
  Briefcase,
  Home,
  MessageCircle,
  Utensils,
  Compass,
  Sparkles,
  Ticket,
  HelpCircle,
  type LucideIcon,
} from 'lucide-react';

interface ServiceIconProps {
  iconName: string;
  size?: number;
  className?: string;
  color?: string;
}

const ICON_MAP: Record<string, LucideIcon> = {
  Newspaper,
  BookOpen,
  ShoppingBag,
  Briefcase,
  Home,
  MessageCircle,
  Utensils,
  Compass,
  Sparkles,
  Ticket,
};

export const ServiceIcon: React.FC<ServiceIconProps> = ({
  iconName,
  size = 22,
  className = '',
  color,
}) => {
  const IconComponent = ICON_MAP[iconName] || HelpCircle;
  return (
    <IconComponent
      size={size}
      className={className}
      style={color ? { color } : undefined}
      aria-hidden="true"
    />
  );
};
