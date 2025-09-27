import type { Community } from './communities'
import type { AddressBook } from './addressBooks'

export interface EventAttributes {
  title: string
  description: string | null
  type: string
  status: string
  createdAt: string
  startDate: string
  endDate: string
  website: string | null
  poster: string
  ticketsUrl: string | null
  cfpUrl: string | null
}

export interface EventRelationships {
  community: Community
  address: AddressBook
}

export interface Event {
  type: 'event'
  id: number
  attributes: EventAttributes
  relationships: EventRelationships
}
