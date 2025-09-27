export interface AddressBookAttributes {
    address_line: string
    city: {
        name: string
        cap: string
    }
    province: {
        name: string
        code: string
    }
    region: {
        name: string
    }
}

export interface AddressBook {
    type: 'address_book'
    id: number
    attributes: AddressBookAttributes
}