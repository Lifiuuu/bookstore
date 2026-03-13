<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    <li class="nav-item nav-profile">
      <a href="{{ route('profile.edit') }}" class="nav-link">
        <div class="nav-profile-image">
          <img src="/assets/images/faces/face1.jpg" alt="profile" />
          <span class="login-status online"></span>
        </div>
        <div class="nav-profile-text d-flex flex-column">
          <span class="font-weight-bold mb-2">{{ Auth::user()->name ?? 'User' }}</span>
          <span class="text-secondary text-small">Admin</span>
        </div>
        <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
      </a>
    </li>
    <li class="nav-item @if(request()->routeIs('dashboard')) active @endif">
      <a class="nav-link" href="{{ route('dashboard') }}">
        <span class="menu-title">Dashboard</span>
        <i class="mdi mdi-home menu-icon"></i>
      </a>
    </li>
    <li class="nav-item @if(request()->routeIs('kategori.*')) active @endif">
      <a class="nav-link" href="{{ route('kategori.index') }}">
        <span class="menu-title">Kategori</span>
        <i class="mdi mdi-book-multiple menu-icon"></i>
      </a>
    </li>
    <li class="nav-item @if(request()->routeIs('buku.*')) active @endif">
      <a class="nav-link" href="{{ route('buku.index') }}">
        <span class="menu-title">Buku</span>
        <i class="mdi mdi-library-shelves menu-icon"></i>
      </a>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-toggle="collapse" href="#pdfGenerator" aria-expanded="@if(request()->routeIs('pdf.*'))true @else false @endif" aria-controls="pdfGenerator">
        <span class="menu-title">Generate Katalog</span>
        <i class="mdi mdi-file-pdf-box menu-icon"></i>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse @if(request()->routeIs('pdf.*')) show @endif" id="pdfGenerator">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item @if(request()->routeIs('pdf.book_catalog_pdf') || request()->routeIs('pdf.book_catalog_download') || request()->routeIs('pdf.book_catalog_preview_portrait')) active @endif">
            <a class="nav-link" href="{{ route('pdf.book_catalog_preview_portrait') }}">Portrait (Text)</a>
          </li>
          <li class="nav-item @if(request()->routeIs('pdf.book_catalog_landscape_pdf') || request()->routeIs('pdf.book_catalog_landscape_download') || request()->routeIs('pdf.book_catalog_preview_landscape')) active @endif">
            <a class="nav-link" href="{{ route('pdf.book_catalog_preview_landscape') }}">Landscape (Certificate)</a>
          </li>
        </ul>
      </div>
    </li>

     <li class="nav-item @if(request()->routeIs('barang.*')) active @endif">
      <a class="nav-link" href="{{ route('barang.index') }}">
        <span class="menu-title">Barang</span>
        <i class="mdi mdi-package menu-icon"></i>
      </a>
    </li>

    <li class="nav-item @if(request()->routeIs('barang.simple')) active @endif">
      <a class="nav-link" href="{{ route('barang.simple') }}">
        <span class="menu-title">Barang Demo (Simple)</span>
        <i class="mdi mdi-table-large menu-icon"></i>
      </a>
    </li>

    <li class="nav-item @if(request()->routeIs('barang.datatables')) active @endif">
      <a class="nav-link" href="{{ route('barang.datatables') }}">
        <span class="menu-title">Barang Demo (DataTables)</span>
        <i class="mdi mdi-table menu-icon"></i>
      </a>
    </li>

    <li class="nav-item @if(request()->routeIs('kota.select')) active @endif">
      <a class="nav-link" href="{{ route('kota.select') }}">
        <span class="menu-title">Kota Select</span>
        <i class="mdi mdi-city menu-icon"></i>
      </a>
    </li>

    <li class="nav-item @if(request()->routeIs('wilayah.index')) active @endif">
      <a class="nav-link" href="{{ route('wilayah.index') }}">
        <span class="menu-title">Wilayah</span>
        <i class="mdi mdi-map menu-icon"></i>
      </a>
    </li>

    <li class="nav-item @if(request()->routeIs('pos.index')) active @endif">
      <a class="nav-link" href="{{ route('pos.index') }}">
        <span class="menu-title">POS</span>
        <i class="mdi mdi-truck menu-icon"></i>
      </a>
    </li>
  </ul>
</nav>