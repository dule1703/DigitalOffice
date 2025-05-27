<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    <p>Welcome, {{ userName }}! This is your dashboard.</p>
    <button @click="logout">Logout</button>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'DashboardView',
  setup() {
    // Reactive state
    const userName = ref('');
    const router = useRouter();

    // Simulate fetching user data on mount
    onMounted(async () => {
      // Replace with actual API call to fetch user data
      try {
        // Simulated API call
        const response = await new Promise((resolve) =>
          setTimeout(() => resolve({ name: 'User' }), 500)
        );
        userName.value = response.name;
      } catch (error) {
        console.error('Failed to fetch user data:', error);
        userName.value = 'Guest';
      }
    });

    // Logout function
    const logout = () => {
      // Replace with actual logout logic (e.g., clear token, API call)
      console.log('Logging out...');
      router.push('/login');
    };

    return {
      userName,
      logout,
    };
  },
};
</script>

<style scoped>
.dashboard {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
  text-align: center;
}
button {
  padding: 10px 20px;
  background-color: #ff4444;
  color: white;
  border: none;
  cursor: pointer;
}
button:hover {
  background-color: #cc0000;
}
</style>