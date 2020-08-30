import { Component, OnInit } from '@angular/core';
import { Router} from '@angular/router';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss']
})
export class HeaderComponent implements OnInit {

  constructor(private  router: Router) { }

  ngOnInit() {
  }

  onChangeMode(mode) {
    switch (mode) {
      case 'map':
        this.router.navigate(['/home']);
        break;
      case 'photo':
        this.router.navigate(['/photo']);
        break;
      case 'gallery':
        this.router.navigate(['/gallery']);
        break;
      default:
        this.router.navigate(['/home']);
        break;
    }
  }
}
