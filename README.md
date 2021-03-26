# block_parser

A python templater that converts HTML with logic blocks (@if-@else, @foreach) into easily/quickly renderable JSON. 

This is an experiement to increase rendering speed of the framework renderer class (Display). 

Initial benchmarks from current rendering showed 5-10x increase in rendering. 

@include nodes created a recursion issue which actually increased rendering time in some cases and the provided data was scoped incorrectly in these cases as well
