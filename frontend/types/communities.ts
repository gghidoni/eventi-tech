export interface CommunityAttributes {
    name: string
    logo: string
}

export interface Community {
    type: 'community'
    id: number
    attributes: CommunityAttributes
}