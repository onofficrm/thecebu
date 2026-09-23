export type MenuCategory = 
  | 'all'
  | 'signature'
  | 'stew_soup'
  | 'gukbap'
  | 'rice'
  | 'noodle'
  | 'chicken'
  | 'side'
  | 'beverage';

export interface MenuItem {
  id: string;
  name: string;
  koreanName: string;
  category: MenuCategory;
  pricePhp: number;
  approxKrw: number;
  description: string;
  highlight?: string;
  image?: string;
  isSignature?: boolean;
  portion: string;
  tags: string[];
}

export interface ReviewItem {
  id: string;
  author: string;
  travelType: string;
  date: string;
  rating: number;
  menuOrdered: string;
  comment: string;
}

export interface ReservationData {
  name: string;
  phone: string;
  date: string;
  time: string;
  guests: number;
  roomType: 'hall' | 'private_room';
  requests: string;
}
