<template>
	<div class="bg-background min-h-screen">
		<Title>{{ title }}</Title>


		<nav class="bg-background text-lg md:px-6 py-6 fixed top-0 left-0 w-full z-50">
			<div class="container mx-auto flex items-center justify-between px-6">
				<NuxtLink to="/">
					<img src="/images/logo.png" alt="Logo" class="w-24">
				</NuxtLink>
				<HamburgerMenu />
			</div>
		</nav>

		<section class="main-content pt-[72px]">
			<slot />
		</section>
	</div>
</template>

<script setup>
const title = useState('title', () => 'Nuxt 3 Blog')
const user = useCookie('user')
</script>

<style>
.router-link-active {
	font-weight: bold;
}
</style>