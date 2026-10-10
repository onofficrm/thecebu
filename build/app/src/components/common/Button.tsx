import React from 'react';
import { LoaderCircle } from 'lucide-react';

type ButtonVariant = 'primary' | 'secondary' | 'outline';

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: ButtonVariant;
  loading?: boolean;
  fullWidth?: boolean;
  leadingIcon?: React.ReactNode;
}

const variantClasses: Record<ButtonVariant, string> = {
  primary: 'border-[#0879E7] bg-[#0879E7] text-white hover:bg-[#066BCF]',
  secondary: 'border-[#C7E9FB] bg-[#EAF8FF] text-[#075A9D] hover:bg-[#D9F1FD]',
  outline: 'border-[#B9E5FC] bg-white text-[#075A9D] hover:bg-[#F1F9FE]',
};

export const Button: React.FC<ButtonProps> = ({
  variant = 'primary',
  loading = false,
  fullWidth = false,
  leadingIcon,
  disabled,
  className = '',
  children,
  ...props
}) => (
  <button
    type="button"
    disabled={disabled || loading}
    aria-busy={loading || undefined}
    className={`inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-bold shadow-xs transition-colors focus:outline-none focus:ring-2 focus:ring-[#079BE8]/25 disabled:cursor-not-allowed disabled:opacity-55 ${
      fullWidth ? 'w-full' : ''
    } ${variantClasses[variant]} ${className}`}
    {...props}
  >
    {loading ? <LoaderCircle size={17} className="animate-spin" aria-hidden="true" /> : leadingIcon}
    <span>{children}</span>
  </button>
);

export const PrimaryButton: React.FC<Omit<ButtonProps, 'variant'>> = (props) => (
  <Button variant="primary" {...props} />
);

export const SecondaryButton: React.FC<Omit<ButtonProps, 'variant'>> = (props) => (
  <Button variant="secondary" {...props} />
);
