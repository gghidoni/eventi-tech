export interface PaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

export interface Pagination {
  currentPage: number
  lastPage: number
  links: PaginationLinks
}
