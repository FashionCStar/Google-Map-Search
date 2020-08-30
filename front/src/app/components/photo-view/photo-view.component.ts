import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'app-photo-view',
  templateUrl: './photo-view.component.html',
  styleUrls: ['./photo-view.component.scss']
})
export class PhotoViewComponent implements OnInit {

  datas = ['dddd', 'gggg', 'hhh', 'ppppp', 'dddd', 'gggg', 'hhh', 'ppppp'];
  constructor() { }

  ngOnInit() {
  }

}
