import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { HomeComponent } from './components/home/home.component';
import { PhotoViewComponent } from './components/photo-view/photo-view.component';
import { GalleryViewComponent } from './components/gallery-view/gallery-view.component';
const routes: Routes = [
  { path: 'home', component: HomeComponent },
  { path: 'photo', component: PhotoViewComponent },
  { path: 'gallery', component: GalleryViewComponent },
    { // Default
          path: '',
          redirectTo: '/home',
          pathMatch: 'full'
    }
];

@NgModule({
  imports: [RouterModule.forRoot(routes, {useHash: true})],
  exports: [RouterModule]
})
export class AppRoutingModule { }
