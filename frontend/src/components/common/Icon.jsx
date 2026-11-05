import {
  Apple, Award, BadgeCheck, Briefcase, Building2, CakeSlice, CalendarDays, CalendarHeart, Camera, ChefHat,
  Cherry, Church, Clock, Coffee, ConciergeBell, Cookie, CookingPot, Crown, Flame, Flower2, Gem, Gift,
  HandPlatter, Handshake, Heart, HeartHandshake, House, IceCreamCone, Landmark, Leaf, Mail, MapPin, Medal,
  Music, PartyPopper, Phone, Salad, ShieldCheck, Smile, Soup, Sparkles, Star, Sun, ThumbsUp, Truck,
  UserRound, Users, Utensils, UtensilsCrossed, Wheat,
} from 'lucide-react'
import { AwardStar, Laddoo, Rings } from './customIcons'

// Same names the admin icon picker offers (backend/admin/_modules.php → ICON_CHOICES).
const ICONS = {
  'chef-hat': ChefHat, 'cooking-pot': CookingPot, soup: Soup, salad: Salad, utensils: Utensils,
  'utensils-crossed': UtensilsCrossed, 'hand-platter': HandPlatter, 'concierge-bell': ConciergeBell,
  flame: Flame, leaf: Leaf, wheat: Wheat, 'cake-slice': CakeSlice, cookie: Cookie, coffee: Coffee,
  'ice-cream-cone': IceCreamCone, cherry: Cherry, apple: Apple, gift: Gift, 'party-popper': PartyPopper,
  sparkles: Sparkles, star: Star, heart: Heart, 'heart-handshake': HeartHandshake, handshake: Handshake,
  gem: Gem, award: Award, medal: Medal, crown: Crown, 'badge-check': BadgeCheck, 'shield-check': ShieldCheck,
  'thumbs-up': ThumbsUp, smile: Smile, users: Users, 'user-round': UserRound, briefcase: Briefcase,
  'building-2': Building2, 'calendar-heart': CalendarHeart, 'calendar-days': CalendarDays, clock: Clock,
  truck: Truck, 'map-pin': MapPin, phone: Phone, mail: Mail, music: Music, camera: Camera,
  'flower-2': Flower2, sun: Sun, house: House, landmark: Landmark, church: Church,
  // custom (Figma)
  'award-star': AwardStar, rings: Rings, laddoo: Laddoo,
}

export default function Icon({ name, size = 20, strokeWidth = 1.8, ...rest }) {
  const Cmp = ICONS[name] || Sparkles
  return <Cmp size={size} strokeWidth={strokeWidth} aria-hidden="true" {...rest} />
}
