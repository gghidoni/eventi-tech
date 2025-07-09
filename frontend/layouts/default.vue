<template>
	<div class="bg-[#2B2B2B] min-h-screen text-gray-900">
		<Title>{{ title }}</Title>


		<nav class="bg-[#2B2B2B] text-lg md:px-6 py-6 fixed top-0 left-0 w-full z-50">
			<div class="container mx-auto flex items-center justify-between px-6">
				<NuxtLink to="/">
					<img src="/images/logo.png" alt="Logo" class="w-24">
				</NuxtLink>
				<HamburgerMenu />
			</div>
		</nav>




		<!-- <nav class="bg-[#2B2B2B] text-lg md:px-6 py-6">
			<div class="container mx-auto flex items-center justify-between px-6">
				<div>
					<NuxtLink to="/"><img src="/images/logo.png" alt="" class="w-24"></NuxtLink>
				</div>
				<div>
					<HamburgerMenu />
				</div> -->
				<!-- <div>
					<ul class="flex space-x-12">
						<li>
							<NuxtLink to="/">Home</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/about">About</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/contact">Contact</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/posts">Posts</NuxtLink>
						</li>
						<li>
							<NuxtLink to="/register">Register</NuxtLink>
						</li>
						<li v-if="!user"><NuxtLink to="/login">Login</NuxtLink></li>
						
						<li v-if="user && user.name" class="text-gray-900">Benvenuto, {{ user.name }}</li>
						<li v-if="user"><NuxtLink to="/logout">Logout</NuxtLink></li>
					</ul>
				</div> -->
			<!-- </div>
		</nav> -->
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