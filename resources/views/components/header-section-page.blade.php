<div class="flex flex-col justify-center items-center">
    <!-- Logo -->
    <img src="{{ asset('./images/logo/logo1.png') }}" class="w-[100px] h-[100px]" alt="Logo">

    <!-- Judul -->
    <h1 class="font-poppins font-bold text-primary text-[40px]">{{ $title }}</h1>

    <!-- Breadcrumb -->
    <div class="flex flex-row space-x-2 font-normal font-dmsans text-[14px] text-[#6b7280]">
        <h2>{{ $breadcrumbHome }}</h2>
        <h2>></h2>
        <h2 class="text-primary">{{ $breadcrumbCurrent }}</h2>
    </div>
</div>