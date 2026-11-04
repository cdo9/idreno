module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        'homiso-green': '#EF4646',
        // 'homiso-green': '#77BC23',
        'homiso-darkgray': '#333333',
        'homiso-lightgray': '#F5F5F5',
        'homiso-gray': '#7A7A7A',
      },
    },
  },
  plugins: [],
};
