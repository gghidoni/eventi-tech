export const useCustomUI = () => {
    const baseInputClasses = 'w-full pr-10 pl-3 h-[36px] border border-gray-400 rounded-md focus:outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 placeholder-gray-500 text-sm bg-transparent'
    const customInputUI = {
        base: `${baseInputClasses} pr-10`
    }
    const customPasswordInputUI = {
        base: `${baseInputClasses} pr-12`,
        trailing: 'pe-1'
    }

    return {
        customInputUI,
        customPasswordInputUI
    }
}