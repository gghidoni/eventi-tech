import type { Event } from './events'

export interface ApiResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
  }
  links: {
    first: string | null
    last: string | null
    prev: string | null
    next: string | null
  }
}
